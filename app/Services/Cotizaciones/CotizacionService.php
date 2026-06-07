<?php

/**
 * @file    CotizacionService.php
 * @package App\Services\Cotizaciones
 *
 * Capa de negocio para la creación, consulta y gestión de cotizaciones.
 * Centraliza la lógica de expiración automática, cálculo de totales
 * y la carga eficiente de ítems y relaciones sin consultas N+1.
 */

namespace App\Services\Cotizaciones;

use App\Models\CotizacionesModel;
use App\Models\CotizacionesDetallesModel;
use App\Models\ColegiosModel;
use App\Models\PromocionesEscolaresModel;
use App\Models\PaquetesModel;
use App\Models\ProductosModel;
use App\Models\ReglasPaquetesModel;

/**
 * Servicio de Cotizaciones.
 *
 * Responsabilidades:
 * - Listar cotizaciones con sus detalles de ítems (paquetes / productos), sin N+1.
 * - Marcar automáticamente como EXPIRADAS las cotizaciones APROBADAS con más de 30 días.
 * - Listar solo las cotizaciones disponibles (APROBADAS sin contrato activo).
 * - Retornar el detalle de una cotización verificando su expiración al vuelo.
 * - Crear cotizaciones en transacción (cabecera + ítems) y auto-crear la promoción escolar.
 * - Actualizar cotizaciones PENDIENTES reemplazando todos sus ítems.
 * - Cambiar el estado de una cotización (PENDIENTE → APROBADA | RECHAZADA | EXPIRADA).
 */
class CotizacionService
{
    /** @var CotizacionesModel Acceso a la tabla `cotizaciones`. */
    protected CotizacionesModel $cotizacionModel;

    /** @var CotizacionesDetallesModel Acceso a la tabla `cotizaciones_detalles`. */
    protected CotizacionesDetallesModel $detalleModel;

    /** @var ColegiosModel Acceso a la tabla `colegios` (auto-creación al cotizar). */
    protected ColegiosModel $colegioModel;

    /** @var PromocionesEscolaresModel Acceso a la tabla `promociones_escolares`. */
    protected PromocionesEscolaresModel $promocionModel;

    /** @var ProductosModel Acceso a la tabla `productos` (resolución de nombres en ítems). */
    protected ProductosModel $productoModel;

    /** @var PaquetesModel Acceso a la tabla `paquetes` (resolución de nombres en ítems). */
    protected PaquetesModel $paqueteModel;

    /** @var ReglasPaquetesModel Acceso a las reglas de bonificación por cantidad. */
    protected ReglasPaquetesModel $reglasPaquetesModel;

    public function __construct()
    {
        $this->cotizacionModel      = new CotizacionesModel();
        $this->detalleModel         = new CotizacionesDetallesModel();
        $this->colegioModel         = new ColegiosModel();
        $this->promocionModel       = new PromocionesEscolaresModel();
        $this->productoModel        = new ProductosModel();
        $this->paqueteModel         = new PaquetesModel();
        $this->reglasPaquetesModel  = new ReglasPaquetesModel();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Carga los ítems de un conjunto de cotizaciones en el mínimo de consultas.
     *
     * Estrategia anti-N+1:
     * 1. Una sola query para todos los detalles del batch.
     * 2. Máximo dos queries batch extra (productos y paquetes) para resolver nombres.
     *
     * @param  int[]                                    $cotizacionIds IDs de las cotizaciones a cargar.
     * @return array<int, array<int, array<string, mixed>>> Detalles indexados por id_cotizacion.
     */
    private function _cargarDetalles(array $cotizacionIds): array
    {
        if (empty($cotizacionIds)) {
            return [];
        }

        $rawDetalles = $this->detalleModel
            ->whereIn('id_cotizacion', $cotizacionIds)
            ->findAll();

        if (empty($rawDetalles)) {
            return [];
        }

        $productoIds = [];
        $paqueteIds  = [];

        foreach ($rawDetalles as $d) {
            $tipo  = strtolower($d['tipo_item'] ?? '');
            $idRef = (int) ($d['id_referencia'] ?? 0);
            if ($idRef === 0) continue;

            if ($tipo === 'producto') {
                $productoIds[] = $idRef;
            } elseif ($tipo === 'paquete') {
                $paqueteIds[] = $idRef;
            }
        }

        $productoMap = [];
        $paqueteMap  = [];

        if (!empty($productoIds)) {
            $prods       = $this->productoModel->whereIn('id_producto', array_unique($productoIds))->findAll();
            $productoMap = array_column($prods, 'nombre_producto', 'id_producto');
        }

        if (!empty($paqueteIds)) {
            $paquetes   = $this->paqueteModel->whereIn('id_paquete', array_unique($paqueteIds))->findAll();
            $paqueteMap = array_column($paquetes, 'nombre_paquete', 'id_paquete');
        }

        $resultado = [];

        foreach ($rawDetalles as $item) {
            $tipo  = strtolower($item['tipo_item'] ?? '');
            $idRef = $item['id_referencia'] ? (int) $item['id_referencia'] : null;

            $referenciaNombre = match ($tipo) {
                'producto' => $idRef ? ($productoMap[$idRef] ?? null) : null,
                'paquete'  => $idRef ? ($paqueteMap[$idRef] ?? null) : null,
                default    => null,
            };

            $resultado[$item['id_cotizacion']][] = [
                'id'                => (int) $item['id_detalle'],
                'tipo_item'         => $tipo,
                'id_referencia'     => $idRef,
                'descripcion'       => $item['descripcion'],
                'cantidad'          => (int) $item['cantidad'],
                'precio_unitario'   => (float) $item['precio_unitario'],
                'referencia_nombre' => $referenciaNombre,
            ];
        }

        return $resultado;
    }

    /**
     * Construye la estructura de salida estándar de una cotización.
     *
     * @param  array<string, mixed> $row           Fila con JOIN de cliente y usuario.
     * @param  array<int, mixed>    $detalles      Ítems ya formateados por _cargarDetalles().
     * @param  bool                 $tieneContrato true si la cotización tiene un contrato activo.
     * @return array<string, mixed>                Estructura: cotizacion, cliente, usuario, detalles.
     */
    private function _formatearCotizacion(
        array  $row,
        array  $detalles,
        bool   $tieneContrato = false,
        ?array $promocion     = null,
        ?array $colegio       = null
    ): array {
        return [
            'cotizacion' => [
                'id'              => (int) $row['id_cotizacion'],
                'fecha'           => $row['fecha_registro'],
                'estado'          => $row['estado'],
                'observaciones'   => $row['observaciones'],
                'total'           => (float) $row['total_estimado'],
                'descuento_monto' => isset($row['descuento_monto']) ? (float) $row['descuento_monto'] : null,
                'tiene_contrato'  => $tieneContrato,
            ],
            'cliente' => [
                'id'               => (int) $row['id_cliente'],
                'nombre_completo'  => trim($row['nombres'] . ' ' . $row['apellidos']),
                'tipo_documento'   => $row['tipo_documento']   ?? null,
                'numero_documento' => $row['numero_documento'] ?? null,
                'telefono'         => $row['telefono']         ?? null,
                'correo'           => $row['correo']           ?? null,
            ],
            'cliente2' => !empty($row['id_cliente2']) ? [
                'id'               => (int) $row['id_cliente2'],
                'nombre_completo'  => trim(($row['nombres2'] ?? '') . ' ' . ($row['apellidos2'] ?? '')),
                'tipo_documento'   => $row['tipo_documento2']   ?? null,
                'numero_documento' => $row['numero_documento2'] ?? null,
                'telefono'         => $row['telefono2']         ?? null,
                'correo'           => $row['correo2']           ?? null,
            ] : null,
            'usuario' => [
                'username' => $row['nombre_user'],
            ],
            'detalles'  => $detalles,
            'promocion' => $promocion ? [
                'id'              => (int) $promocion['id_promocion'],
                'nombre'          => $promocion['nombre'],
                'num_estudiantes' => (int) $promocion['num_estudiantes'],
            ] : null,
            'colegio' => $colegio ? [
                'id'       => (int) $colegio['id_colegio'],
                'nombre'   => $colegio['nombre_colegio'],
                'provincia'=> $colegio['provincia'],
                'distrito' => $colegio['distrito'],
            ] : null,
        ];
    }

    /**
     * Crea automáticamente una promoción escolar al generar una cotización.
     *
     * Requiere que $data contenga las claves 'sesion' y 'colegio'.
     * Si faltan datos o el insert falla, el error se silencia sin afectar
     * la cotización ya confirmada en BD.
     *
     * @param  int                  $idCotizacion ID de la cotización recién creada.
     * @param  array<string, mixed> $data         Payload completo de la cotización (incluye sesion/colegio).
     * @return void
     */
    private function _crearPromocionDesde(int $idCotizacion, array $data): void
    {
        $sesion  = $data['sesion']  ?? [];
        $colegio = $data['colegio'] ?? [];

        $numEstudiantes = (int) ($sesion['num_estudiantes'] ?? 0);
        $nombreColegio  = trim($colegio['nombre'] ?? '');

        if ($numEstudiantes <= 0 || $nombreColegio === '') {
            log_message('warning', "[CotizacionService] _crearPromocionDesde #{$idCotizacion}: datos insuficientes (num_estudiantes={$numEstudiantes}, nombre='{$nombreColegio}')");
            return;
        }

        try {
            $idColegio = $this->_encontrarOCrearColegio($nombreColegio, $colegio);
            if ($idColegio === null) {
                log_message('error', "[CotizacionService] _crearPromocionDesde #{$idCotizacion}: no se pudo crear el colegio '{$nombreColegio}'");
                return;
            }

            $gradosValidos = ['Inicial', 'Jardin', 'Primaria', 'Secundaria', 'Superior', 'Otro'];
            $gradoRaw = $sesion['grado'] ?? '';
            $grado    = in_array($gradoRaw, $gradosValidos, true) ? $gradoRaw : 'Otro';
            $seccion = !empty($sesion['seccion']) ? $sesion['seccion'] : null;
            $anio    = !empty($sesion['fecha']) ? (int) date('Y', strtotime($sesion['fecha'])) : (int) date('Y');
            $nombre  = !empty($sesion['nombre_promocion'])
                ? trim($sesion['nombre_promocion'])
                : ($nombreColegio . ' ' . $grado . ($seccion ? ' ' . $seccion : '') . ' ' . $anio);

            $this->promocionModel->insert([
                'id_colegio'      => $idColegio,
                'id_cotizacion'   => $idCotizacion,
                'nombre'          => $nombre,
                'grado'           => $grado,
                'seccion'         => $seccion,
                'num_estudiantes' => $numEstudiantes,
                'anio'            => $anio,
                'is_active'       => false,
            ]);

            $errors = $this->promocionModel->errors();
            if (!empty($errors)) {
                log_message('error', "[CotizacionService] _crearPromocionDesde #{$idCotizacion}: " . json_encode($errors));
            }
        } catch (\Throwable $e) {
            log_message('error', "[CotizacionService] _crearPromocionDesde #{$idCotizacion}: {$e->getMessage()}");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lista todas las cotizaciones con sus ítems y la bandera de contrato activo.
     *
     * Ejecuta la expiración automática antes de leer para que el estado
     * almacenado en BD refleje siempre la realidad al momento del listado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listar(): array
    {
        $this->cotizacionModel->expirarAntiguas();
        $rows = $this->cotizacionModel->listarConCliente();

        if (empty($rows)) {
            return [];
        }

        $ids            = array_column($rows, 'id_cotizacion');
        $detallesPorCot = $this->_cargarDetalles($ids);
        $conContrato    = array_flip($this->cotizacionModel->idsCotizacionesConContrato($ids));
        $cotizaciones   = [];

        foreach ($rows as $row) {
            $id             = $row['id_cotizacion'];
            $cotizaciones[] = $this->_formatearCotizacion(
                $row,
                $detallesPorCot[$id] ?? [],
                isset($conContrato[$id])
            );
        }

        return $cotizaciones;
    }

    /**
     * Lista únicamente cotizaciones APROBADAS que no tienen contrato activo.
     *
     * Utilizado por el selector de cotizaciones al generar un contrato,
     * para evitar mostrar opciones que ya fueron contratadas.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarDisponibles(): array
    {
        $rows = $this->cotizacionModel->listarAprobadasSinContrato();

        if (empty($rows)) {
            return [];
        }

        $ids            = array_column($rows, 'id_cotizacion');
        $detallesPorCot = $this->_cargarDetalles($ids);
        $cotizaciones   = [];

        foreach ($rows as $row) {
            $id             = $row['id_cotizacion'];
            $cotizaciones[] = $this->_formatearCotizacion($row, $detallesPorCot[$id] ?? []);
        }

        return $cotizaciones;
    }

    /**
     * Retorna el detalle completo de una cotización individual.
     *
     * Verifica la expiración puntual antes de leer para garantizar
     * que el estado sea fresco incluso si el listado no fue llamado recientemente.
     *
     * @param  int                       $idCotizacion ID de la cotización.
     * @return array<string, mixed>|null               null si no existe.
     */
    public function obtenerPorId(int $idCotizacion): ?array
    {
        $this->cotizacionModel->verificarExpiracion($idCotizacion);

        $row = $this->cotizacionModel->obtenerConCliente($idCotizacion);

        if (!$row) {
            return null;
        }

        $detallesPorCot = $this->_cargarDetalles([$idCotizacion]);
        $conContrato    = $this->cotizacionModel->idsCotizacionesConContrato([$idCotizacion]);

        $promocion = $this->promocionModel->where('id_cotizacion', $idCotizacion)->first();
        $colegio   = ($promocion && !empty($promocion['id_colegio']))
            ? $this->colegioModel->find($promocion['id_colegio'])
            : null;

        $detalles = $detallesPorCot[$idCotizacion] ?? [];

        $result = $this->_formatearCotizacion(
            $row,
            $detalles,
            !empty($conContrato),
            $promocion,
            $colegio
        );

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea una cotización completa (cabecera + ítems) en una sola transacción.
     *
     * Después de confirmar la transacción, intenta crear automáticamente
     * la promoción escolar a partir de los datos del payload (best-effort).
     *
     * Estructura esperada en $data:
     * - id_cliente, id_usuario, total_estimado, observaciones?
     * - detalles[]: { tipo_item, id_referencia?, descripcion, cantidad, precio_unitario }
     * - sesion?:    { grado, num_estudiantes, fecha?, seccion?, nombre_promocion? }
     * - colegio?:   { nombre, provincia?, distrito? }
     *
     * @param  array<string, mixed> $data Payload del formulario de cotización.
     * @return array<string, mixed>       Cotización recién creada con todos sus datos.
     *
     * @throws \RuntimeException 422 si falla la validación del modelo de cabecera.
     * @throws \RuntimeException 500 si falla la inserción de detalles o la transacción.
     */
    public function crear(array $data): array
    {
        $numEstudiantes = (int) (($data['sesion'] ?? [])['num_estudiantes'] ?? 0);
        if ($numEstudiantes <= 0 || $numEstudiantes > 1000) {
            throw new \RuntimeException('El número de estudiantes es obligatorio y debe estar entre 1 y 1000', 422);
        }

        $evaluacion = $this->reglasPaquetesModel->evaluarDetalles($data['detalles'] ?? []);
        if (!empty($evaluacion['violaciones'])) {
            $msgs = implode('; ', array_column($evaluacion['violaciones'], 'descripcion'));
            throw new \RuntimeException("Límite de cantidad superado: {$msgs}", 422);
        }

        $db = $this->cotizacionModel->db;
        $db->transStart();

        $idCotizacion = $this->cotizacionModel->insert([
            'id_cliente'      => $data['id_cliente'],
            'id_cliente2'     => !empty($data['id_cliente2']) ? (int) $data['id_cliente2'] : null,
            'id_usuario'      => $data['id_usuario'],
            'observaciones'   => $data['observaciones'] ?? null,
            'fecha_registro'  => date('Y-m-d H:i:s'),
            'total_estimado'  => $data['total_estimado'],
            'descuento_monto' => isset($data['descuento_monto']) ? (float) $data['descuento_monto'] : null,
            'estado'          => 'PENDIENTE',
        ]);

        if ($idCotizacion === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->cotizacionModel->errors()), 422);
        }

        $detallesInsert = [];
        foreach ($data['detalles'] as $detalle) {
            $detallesInsert[] = [
                'tipo_item'       => $detalle['tipo_item'],
                'id_referencia'   => $detalle['id_referencia'] ?? null,
                'descripcion'     => $detalle['descripcion'],
                'cantidad'        => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario'],
                'id_cotizacion'   => $idCotizacion,
            ];
        }

        if (!empty($detallesInsert)) {
            $ok = $this->detalleModel->insertBatch($detallesInsert);
            if ($ok === false) {
                $db->transRollback();
                throw new \RuntimeException(
                    'Error al insertar detalles: ' . implode(', ', $this->detalleModel->errors()),
                    500
                );
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al crear la cotización', 500);
        }

        $this->_crearPromocionDesde($idCotizacion, $data);

        return $this->obtenerPorId($idCotizacion);
    }

    /**
     * Actualiza cabecera e ítems de una cotización PENDIENTE.
     *
     * Reemplaza completamente los ítems existentes (delete + insertBatch)
     * en la misma transacción para mantener la consistencia del total.
     *
     * @param  int                  $idCotizacion ID de la cotización.
     * @param  array<string, mixed> $data         Nuevos valores: total_estimado, observaciones?, detalles[].
     * @return array<string, mixed>               Cotización actualizada con todos sus datos.
     *
     * @throws \RuntimeException 404 si la cotización no existe.
     * @throws \RuntimeException 409 si la cotización no está en estado PENDIENTE.
     * @throws \RuntimeException 500 si falla el guardado de detalles o la transacción.
     */
    public function actualizar(int $idCotizacion, array $data): array
    {
        $cotizacion = $this->cotizacionModel->find($idCotizacion);

        if (!$cotizacion) {
            throw new \RuntimeException('Cotización no encontrada', 404);
        }

        if ($cotizacion['estado'] !== 'PENDIENTE') {
            throw new \RuntimeException('Solo se puede editar una cotización PENDIENTE', 409);
        }

        $db = $this->cotizacionModel->db;
        $db->transStart();

        $camposUpdate = [
            'observaciones' => $data['observaciones'] ?? $cotizacion['observaciones'],
        ];
        if (isset($data['total_estimado'])) {
            $camposUpdate['total_estimado'] = $data['total_estimado'];
        }
        if (array_key_exists('descuento_monto', $data)) {
            $camposUpdate['descuento_monto'] = isset($data['descuento_monto']) ? (float) $data['descuento_monto'] : null;
        }
        if (array_key_exists('id_cliente2', $data)) {
            $camposUpdate['id_cliente2'] = !empty($data['id_cliente2']) ? (int) $data['id_cliente2'] : null;
        }
        $this->cotizacionModel->update($idCotizacion, $camposUpdate);

        if (!empty($data['detalles'])) {
            $this->detalleModel->where('id_cotizacion', $idCotizacion)->delete();
        }

        if (!empty($data['detalles'])) {
            $detallesInsert = [];
            foreach ($data['detalles'] as $detalle) {
                $detallesInsert[] = [
                    'tipo_item'       => $detalle['tipo_item'],
                    'id_referencia'   => $detalle['id_referencia'] ?? null,
                    'descripcion'     => $detalle['descripcion'],
                    'cantidad'        => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'id_cotizacion'   => $idCotizacion,
                ];
            }

            $ok = $this->detalleModel->insertBatch($detallesInsert);
            if ($ok === false) {
                $db->transRollback();
                throw new \RuntimeException(
                    'Error al guardar detalles: ' . implode(', ', $this->detalleModel->errors()),
                    500
                );
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al actualizar la cotización', 500);
        }

        $this->_actualizarPromocionYColegio($idCotizacion, $data);

        return $this->obtenerPorId($idCotizacion);
    }

    /**
     * Actualiza la promoción y el colegio vinculados a una cotización si se proporcionan datos.
     * Se ejecuta fuera de la transacción principal; los errores se silencian para
     * no revertir los cambios de ítems ya confirmados.
     *
     * @param  int                  $idCotizacion
     * @param  array<string, mixed> $data Puede contener 'promocion' y/o 'colegio'.
     * @return void
     */
    private function _actualizarPromocionYColegio(int $idCotizacion, array $data): void
    {
        try {
            $promoData   = is_array($data['promocion'] ?? null) ? $data['promocion'] : [];
            $colegioData = is_array($data['colegio']   ?? null) ? $data['colegio']   : [];

            $promocion = $this->promocionModel->where('id_cotizacion', $idCotizacion)->first();

            if (!$promocion) {
                $nombre        = trim($promoData['nombre']          ?? '');
                $numEst        = (int) ($promoData['num_estudiantes'] ?? 0);
                $nombreColegio = trim($colegioData['nombre']          ?? '');

                if (($nombre === '' && $numEst <= 0) || $nombreColegio === '') {
                    return;
                }

                $idColegio = $this->_encontrarOCrearColegio($nombreColegio, $colegioData);
                if ($idColegio === null) {
                    return;
                }

                $db = $this->cotizacionModel->db;
                $db->table('promociones_escolares')->insert([
                    'id_colegio'      => $idColegio,
                    'id_cotizacion'   => $idCotizacion,
                    'nombre'          => $nombre !== '' ? $nombre : ('Promoción ' . date('Y')),
                    'grado'           => 'Otro',
                    'seccion'         => null,
                    'num_estudiantes' => max(1, $numEst),
                    'anio'            => (int) date('Y'),
                    'is_active'       => 1,
                ]);
                return;
            }

            $promoUpdate = [];
            if (isset($promoData['nombre'])) {
                $promoUpdate['nombre'] = $promoData['nombre'];
            }
            if (isset($promoData['num_estudiantes']) && (int) $promoData['num_estudiantes'] > 0) {
                $promoUpdate['num_estudiantes'] = (int) $promoData['num_estudiantes'];
            }
            if (!empty($promoUpdate)) {
                $this->promocionModel->update($promocion['id_promocion'], $promoUpdate);
            }

            if (!empty($colegioData)) {
                $colegioUpdate = [];
                if (!empty($colegioData['nombre'])) {
                    $colegioUpdate['nombre_colegio'] = trim($colegioData['nombre']);
                }
                if (array_key_exists('provincia', $colegioData)) {
                    $colegioUpdate['provincia'] = $colegioData['provincia'] ?: null;
                }
                if (array_key_exists('distrito', $colegioData)) {
                    $colegioUpdate['distrito'] = $colegioData['distrito'] ?: null;
                }
                if (!empty($colegioUpdate)) {
                    if (!empty($promocion['id_colegio'])) {
                        $this->colegioModel->update($promocion['id_colegio'], $colegioUpdate);
                    } else {
                        $nombreCol = trim($colegioData['nombre'] ?? '');
                        if ($nombreCol !== '') {
                            $idColegio = $this->_encontrarOCrearColegio($nombreCol, $colegioData);
                            if ($idColegio !== null) {
                                $this->promocionModel->update($promocion['id_promocion'], ['id_colegio' => $idColegio]);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[CotizacionService] _actualizarPromocionYColegio id=' . $idCotizacion . ': ' . $e->getMessage());
        }
    }

    /**
     * Busca un colegio por nombre (case-insensitive) o lo crea si no existe.
     *
     * @param  string               $nombre      Nombre del colegio.
     * @param  array<string, mixed> $colegioData Datos extra (provincia, distrito).
     * @return int|null                          ID del colegio, o null si falla el insert.
     */
    private function _encontrarOCrearColegio(string $nombre, array $colegioData): ?int
    {
        $existente = $this->colegioModel
            ->where('LOWER(nombre_colegio)', strtolower($nombre))
            ->first();

        if ($existente) {
            return (int) $existente['id_colegio'];
        }

        $id = $this->colegioModel->insert([
            'nombre_colegio' => $nombre,
            'provincia'      => $colegioData['provincia'] ?? null,
            'distrito'       => $colegioData['distrito']  ?? null,
            'estado'         => 'ACTIVO',
        ]);

        return $id !== false ? (int) $id : null;
    }

    /**
     * Cambia el estado de una cotización.
     *
     * Los estados válidos son los definidos en el ENUM de la tabla:
     * PENDIENTE | APROBADA | RECHAZADA | EXPIRADA.
     *
     * @param  int    $idCotizacion ID de la cotización.
     * @param  string $estado       Nuevo estado a aplicar.
     * @return void
     */
    public function cambiarEstado(int $idCotizacion, string $estado): void
    {
        if (!$this->cotizacionModel->find($idCotizacion)) {
            throw new \RuntimeException('Cotización no encontrada', 404);
        }
        $this->cotizacionModel->update($idCotizacion, ['estado' => $estado]);
    }
}
