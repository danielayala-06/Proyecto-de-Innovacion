<?php

namespace App\Services\Cotizaciones;

use App\Models\CotizacionesModel;
use App\Models\CotizacionesDetallesModel;
use App\Models\ColegiosModel;
use App\Models\PromocionesEscolaresModel;
use App\Models\PaquetesModel;
use App\Models\ProductosModel;

class CotizacionService
{
    protected CotizacionesModel         $cotizacionModel;
    protected CotizacionesDetallesModel $detalleModel;
    protected ColegiosModel             $colegioModel;
    protected PromocionesEscolaresModel $promocionModel;
    protected ProductosModel            $productoModel;
    protected PaquetesModel             $paqueteModel;

    public function __construct()
    {
        $this->cotizacionModel = new CotizacionesModel();
        $this->detalleModel    = new CotizacionesDetallesModel();
        $this->colegioModel    = new ColegiosModel();
        $this->promocionModel  = new PromocionesEscolaresModel();
        $this->productoModel   = new ProductosModel();
        $this->paqueteModel    = new PaquetesModel();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Carga detalles (con nombres de referencia) para un conjunto de cotizaciones.
    // Resuelve el problema N+1: 1 query para detalles + max 2 queries para nombres.
    // Retorna array indexado por id_cotizacion.
    // ─────────────────────────────────────────────────────────────────────────
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

        // Colectar IDs únicos por tipo para carga batch
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

        // Agrupar por id_cotizacion construyendo la estructura final
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

    // ─────────────────────────────────────────────────────────────────────────
    // Construye el array de salida estándar de una cotización
    // ─────────────────────────────────────────────────────────────────────────
    private function _formatearCotizacion(array $row, array $detalles): array
    {
        return [
            'cotizacion' => [
                'id'           => (int) $row['id_cotizacion'],
                'fecha'        => $row['fecha_registro'],
                'estado'       => $row['estado'],
                'observaciones'=> $row['observaciones'],
                'total'        => (float) $row['total_estimado'],
            ],
            'cliente' => [
                'id'             => (int) $row['id_cliente'],
                'nombre_completo'=> trim($row['nombres'] . ' ' . $row['apellidos']),
            ],
            'usuario' => [
                'username' => $row['nombre_user'],
            ],
            'detalles' => $detalles,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Listado general — sin N+1
    // ─────────────────────────────────────────────────────────────────────────
    public function listar(): array
    {
        $rows = $this->cotizacionModel->listarConCliente();

        if (empty($rows)) {
            return [];
        }

        $ids               = array_column($rows, 'id_cotizacion');
        $detallesPorCot    = $this->_cargarDetalles($ids);
        $cotizaciones      = [];

        foreach ($rows as $row) {
            $id             = $row['id_cotizacion'];
            $cotizaciones[] = $this->_formatearCotizacion($row, $detallesPorCot[$id] ?? []);
        }

        return $cotizaciones;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Detalle de una cotización — sin N+1
    // ─────────────────────────────────────────────────────────────────────────
    public function obtenerPorId(int $idCotizacion): ?array
    {
        $row = $this->cotizacionModel->obtenerConCliente($idCotizacion);

        if (!$row) {
            return null;
        }

        $detallesPorCot = $this->_cargarDetalles([$idCotizacion]);

        return $this->_formatearCotizacion($row, $detallesPorCot[$idCotizacion] ?? []);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Crear cotización completa
    // ─────────────────────────────────────────────────────────────────────────
    public function crear(array $data): array
    {
        $db = $this->cotizacionModel->db;
        $db->transStart();

        $idCotizacion = $this->cotizacionModel->insert([
            'id_cliente'     => $data['id_cliente'],
            'id_usuario'     => $data['id_usuario'],
            'observaciones'  => $data['observaciones'] ?? null,
            'fecha_registro' => date('Y-m-d H:i:s'),
            'total_estimado' => $data['total_estimado'],
            'estado'         => 'PENDIENTE',
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
                    'Error al insertar detalles: ' . implode(', ', $this->detalleModel->errors()), 500
                );
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al crear la cotización', 500);
        }

        // Auto-crear promoción a partir de los datos de sesión/colegio (best-effort)
        $this->_crearPromocionDesde($idCotizacion, $data);

        return $this->obtenerPorId($idCotizacion);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Crea automáticamente una promoción escolar al generar una cotización.
    // Si faltan datos o ocurre cualquier error, se ignora silenciosamente.
    // ─────────────────────────────────────────────────────────────────────────
    private function _crearPromocionDesde(int $idCotizacion, array $data): void
    {
        $sesion  = $data['sesion']  ?? [];
        $colegio = $data['colegio'] ?? [];

        $grado          = $sesion['grado']           ?? null;
        $numEstudiantes = (int) ($sesion['num_estudiantes'] ?? 0);
        $nombreColegio  = trim($colegio['nombre']    ?? '');

        if (!$grado || $numEstudiantes <= 0 || $nombreColegio === '') {
            return;
        }

        try {
            // Buscar colegio existente por nombre (case-insensitive)
            $colegioRow = $this->colegioModel
                ->where('LOWER(nombre_colegio)', strtolower($nombreColegio))
                ->first();

            if ($colegioRow) {
                $idColegio = $colegioRow['id_colegio'];
            } else {
                $idColegio = $this->colegioModel->insert([
                    'nombre_colegio' => $nombreColegio,
                    'provincia'      => $colegio['provincia'] ?? null,
                    'distrito'       => $colegio['distrito']  ?? null,
                    'estado'         => 'ACTIVO',
                ]);
                if ($idColegio === false) return;
            }

            $anio   = !empty($sesion['fecha']) ? (int) date('Y', strtotime($sesion['fecha'])) : (int) date('Y');
            $seccion = $sesion['seccion'] ?? null;
            $nombre  = !empty($sesion['nombre_promocion'])
                ? $sesion['nombre_promocion']
                : ($grado . ($seccion ? ' ' . $seccion : '') . ' · ' . $anio);

            $this->promocionModel->insert([
                'id_colegio'      => $idColegio,
                'id_cotizacion'   => $idCotizacion,
                'nombre'          => $nombre,
                'grado'           => $grado,
                'seccion'         => $seccion,
                'num_estudiantes' => $numEstudiantes,
                'anio'            => $anio,
                'is_active'       => true,
            ]);
        } catch (\Throwable) {
            // Silently ignore: the cotización is already committed
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Actualizar cotización: reemplaza detalles y recalcula totales
    // ─────────────────────────────────────────────────────────────────────────
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

        // Actualizar cabecera
        $this->cotizacionModel->update($idCotizacion, [
            'observaciones'  => $data['observaciones'] ?? $cotizacion['observaciones'],
            'total_estimado' => $data['total_estimado'],
        ]);

        // Reemplazar detalles: eliminar existentes e insertar los nuevos
        $this->detalleModel->where('id_cotizacion', $idCotizacion)->delete();

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
                    'Error al guardar detalles: ' . implode(', ', $this->detalleModel->errors()), 500
                );
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al actualizar la cotización', 500);
        }

        return $this->obtenerPorId($idCotizacion);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cambiar solo el estado
    // ─────────────────────────────────────────────────────────────────────────
    public function cambiarEstado(int $idCotizacion, string $estado): void
    {
        $this->cotizacionModel->update($idCotizacion, ['estado' => $estado]);
    }
}
