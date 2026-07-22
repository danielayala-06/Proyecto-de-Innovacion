<?php

/**
 * @file    EstudianteService.php
 * @package App\Services\Estudiantes
 *
 * Capa de negocio para el registro y gestión de estudiantes.
 * Cada estudiante se crea en una transacción que involucra tres tablas:
 * personas (apoderado), apoderados y estudiantes.
 */

namespace App\Services\Estudiantes;

use App\Models\EstudiantesModel;
use App\Models\PersonasModel;
use App\Models\PromocionesEscolaresModel;
use App\Models\ApoderadosModel;

/**
 * Servicio de Estudiantes.
 *
 * Responsabilidades:
 * - Listar estudiantes de una promoción con datos del apoderado.
 * - Crear un estudiante en una sola transacción (persona → apoderado → estudiante).
 * - Actualizar datos propios del estudiante (no del apoderado).
 * - Eliminar un estudiante (la asistencia se borra en cascada por FK).
 */
class EstudianteService
{
    /** @var EstudiantesModel Acceso a la tabla `estudiantes`. */
    protected EstudiantesModel $estudianteModel;

    /** @var PersonasModel Acceso a la tabla `personas` (datos del apoderado). */
    protected PersonasModel $personaModel;

    /** @var PromocionesEscolaresModel Acceso a la tabla `promociones_escolares`. */
    protected PromocionesEscolaresModel $promocionModel;

    /** @var ApoderadosModel Acceso a la tabla `apoderados`. */
    protected ApoderadosModel $apoderadoModel;

    public function __construct()
    {
        $this->estudianteModel = model(EstudiantesModel::class);
        $this->personaModel    = model(PersonasModel::class);
        $this->promocionModel  = model(PromocionesEscolaresModel::class);
        $this->apoderadoModel  = model(ApoderadosModel::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STOCK
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Devuelve el stock de cada producto del paquete contratado para una promoción.
     *
     * Total: derivado de cotizaciones_detalles → paquetes → paquetes_productos.
     * Usado: contado desde estudiante_productos JOIN estudiantes de esa promoción.
     *
     * @param  int $idPromocion
     * @return array<int, array{
     *   id_producto:     int,
     *   nombre_producto: string,
     *   categoria:       string,
     *   total:           int,
     *   disponible:      int
     * }>
     */
    public function stockPorPromocion(int $idPromocion): array
    {
        $db = \Config\Database::connect();

        // Totales de cada producto según el paquete contratado
        $totales = $db->query(
            'SELECT
                pr.id_producto,
                pr.nombre_producto,
                pr.categoria,
                SUM(cd.cantidad * pp.cantidad) AS total
             FROM cotizaciones_detalles cd
             JOIN paquetes             pk ON pk.id_paquete   = cd.id_referencia
             JOIN paquetes_productos   pp ON pp.id_paquete   = pk.id_paquete
             JOIN productos            pr ON pr.id_producto  = pp.id_producto
             JOIN promociones_escolares pe ON pe.id_cotizacion = cd.id_cotizacion
             WHERE pe.id_promocion = ?
               AND cd.tipo_item = "paquete"
             GROUP BY pr.id_producto, pr.nombre_producto, pr.categoria',
            [$idPromocion]
        )->getResultArray();

        if (empty($totales)) {
            return [];
        }

        // Unidades ya asignadas a estudiantes de esta promoción
        $ids     = array_column($totales, 'id_producto');
        $usadosR = $db->query(
            'SELECT ep.id_producto, COUNT(*) AS usados
             FROM estudiante_productos ep
             JOIN estudiantes e ON e.id_estudiante = ep.id_estudiante
             WHERE e.id_promocion = ?
               AND ep.id_producto IN (' . implode(',', array_fill(0, count($ids), '?')) . ')
             GROUP BY ep.id_producto',
            array_merge([$idPromocion], $ids)
        )->getResultArray();

        $usados = array_column($usadosR, 'usados', 'id_producto');

        $resultado = [];
        foreach ($totales as $row) {
            $id    = (int) $row['id_producto'];
            $total = (int) $row['total'];
            $usado = (int) ($usados[$id] ?? 0);

            $resultado[] = [
                'id_producto'     => $id,
                'nombre_producto' => $row['nombre_producto'],
                'categoria'       => $row['categoria'],
                'total'           => $total,
                'disponible'      => max(0, $total - $usado),
            ];
        }

        return $resultado;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna los estudiantes de una promoción con datos de su apoderado.
     *
     * @param  int $idPromocion ID de la promoción.
     * @return array<int, array<string, mixed>>
     */
    public function listarPorPromocion(int $idPromocion): array
    {
        return $this->estudianteModel->listarConApoderado($idPromocion);
    }

    /**
     * Retorna el perfil completo de un estudiante:
     *   - Datos personales + apoderado.
     *   - Productos contratados en la cotización de su promoción.
     *   - Historial de asistencia a sesiones fotográficas.
     *
     * @param  int                       $id ID del estudiante.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerDetalle(int $id): ?array
    {
        $estudiante = $this->estudianteModel->obtenerConApoderado($id);
        if (!$estudiante) {
            return null;
        }

        $db = \Config\Database::connect();

        // Productos asignados a este estudiante específicamente
        $estudiante['productos'] = $db
            ->table('estudiante_productos ep')
            ->select('pr.id_producto, pr.nombre_producto, pr.categoria')
            ->join('productos pr', 'pr.id_producto = ep.id_producto')
            ->where('ep.id_estudiante', $id)
            ->get()->getResultArray();

        // Historial de asistencia del estudiante
        // Castear asistio a int|null para que el JSON sea tipado (MySQLi devuelve strings)
        $sesiones = $db
            ->table('sesion_asistencia sa')
            ->select('sf.id_sesion, sf.tipo, sf.fecha_hora_sesion, sf.estado, sa.asistio')
            ->join('sesiones_fotograficas sf', 'sf.id_sesion = sa.id_sesion')
            ->where('sa.id_estudiante', $id)
            ->orderBy('sf.fecha_hora_sesion', 'ASC')
            ->get()->getResultArray();

        $estudiante['sesiones'] = array_map(function ($s) {
            $s['asistio'] = $s['asistio'] !== null ? (int) $s['asistio'] : null;
            return $s;
        }, $sesiones);

        return $estudiante;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Registra un estudiante junto a su apoderado en una sola transacción.
     *
     * Flujo de inserción:
     * 1. Crea la persona del apoderado en `personas`.
     * 2. Crea el registro en `apoderados` vinculado a la persona.
     * 3. Crea el estudiante vinculado al apoderado y a la promoción.
     *
     * Estructura esperada en $data:
     * - id_promocion  (int)
     * - productos     (int[], opcional) — IDs de productos asignados al estudiante
     * - estudiante: { nombres, apellidos, fecha_nacimiento?, color_fav?, profesion_futura? }
     * - apoderado:  { nombres, apellidos?, telefono, correo?, tipo_relacion,
     *                 tipo_documento, numero_documento }
     *
     * @param  array<string, mixed> $data Payload completo del formulario.
     * @return int                        ID del estudiante creado.
     *
     * @throws \RuntimeException 404 si la promoción no existe.
     * @throws \RuntimeException 422 si faltan datos o falla validación del modelo.
     * @throws \RuntimeException 500 si la transacción no se confirma.
     */
    public function crear(array $data): int
    {
        $idPromocion      = (int) ($data['id_promocion'] ?? 0);
        $productosIds     = array_values(array_filter(
            array_map('intval', (array) ($data['productos'] ?? [])),
            fn($id) => $id > 0
        ));

        if (!$this->promocionModel->find($idPromocion)) {
            throw new \RuntimeException('Promoción no encontrada', 404);
        }

        // Validar stock disponible antes de abrir la transacción
        if (!empty($productosIds)) {
            $stockActual = $this->stockPorPromocion($idPromocion);
            $stockMap    = array_column($stockActual, null, 'id_producto');

            foreach ($productosIds as $idProd) {
                if (!isset($stockMap[$idProd])) {
                    throw new \RuntimeException("Producto {$idProd} no pertenece a esta promoción.", 422);
                }
                if ($stockMap[$idProd]['disponible'] <= 0) {
                    $nombre = $stockMap[$idProd]['nombre_producto'];
                    throw new \RuntimeException("Sin stock disponible para: {$nombre}.", 409);
                }
            }
        }

        $apData = $data['apoderado'] ?? [];
        $esData = $data['estudiante'] ?? [];

        // Normalizar a mayúsculas campos de texto (UTF-8)
        $upper = function ($v) {
            if ($v === null) return null;
            $v = trim((string) $v);
            return mb_strtoupper($v, 'UTF-8');
        };

        if (!empty($apData)) {
            $apData['nombres']   = $upper($apData['nombres'] ?? null);
            $apData['apellidos'] = $upper($apData['apellidos'] ?? null);
            if (!empty($apData['correo'])) $apData['correo'] = trim($apData['correo']);
            if (!empty($apData['numero_documento'])) $apData['numero_documento'] = trim($apData['numero_documento']);
        }

        if (!empty($esData)) {
            $esData['nombres']   = $upper($esData['nombres'] ?? null);
            $esData['apellidos'] = $upper($esData['apellidos'] ?? null);
        }

        if (empty($apData) || empty($esData)) {
            throw new \RuntimeException('Datos del apoderado y del estudiante son requeridos', 422);
        }

        // Prevención de reenvío: si ya existe un estudiante para la misma promoción
        // con el mismo teléfono o número de documento del apoderado, abortar.
        $telefono = $apData['telefono'] ?? null;
        $numeroDoc = $apData['numero_documento'] ?? null;

        if (!empty($telefono) || !empty($numeroDoc)) {
            $qb = $this->estudianteModel->builder();
            $qb->select('estudiantes.id_estudiante')
                ->join('apoderados a', 'a.id_apoderado = estudiantes.id_apoderado')
                ->join('personas p', 'p.id_persona = a.id_persona')
                ->where('estudiantes.id_promocion', $idPromocion);

            if (!empty($telefono)) {
                $qb->where('p.telefono', $telefono);
            }

            if (!empty($numeroDoc)) {
                $qb->orWhere('p.numero_documento', $numeroDoc);
            }

            $existing = $qb->get()->getRowArray();
            if (!empty($existing)) {
                throw new \RuntimeException('Ya existe un estudiante registrado con ese número de teléfono en esta promoción.', 409);
            }
        }

        $db = $this->personaModel->db;
        $db->transStart();

        // 1. Persona del apoderado
        $idPersona = $this->personaModel->insert([
            'nombres'          => $apData['nombres'],
            'apellidos'        => $apData['apellidos'] ?? '',
            'telefono'         => $apData['telefono'],
            'correo'           => $apData['correo'] ?? null,
            'numero_documento' => $apData['numero_documento'] ?? null,
            'tipo_documento'   => $apData['tipo_documento'] ?? null,
        ]);

        if ($idPersona === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->personaModel->errors()), 422);
        }

        // 2. Apoderado
        $idApoderado = $this->apoderadoModel->insert([
            'id_persona'    => $idPersona,
            'tipo_relacion' => $apData['tipo_relacion'] ?? 'otro',
        ]);

        if ($idApoderado === false) {
            $db->transRollback();
            throw new \RuntimeException('Error al crear el apoderado', 500);
        }

        // 3. Estudiante
        if (!empty($esData['fecha_nacimiento'])) {
            $fn = \DateTime::createFromFormat('Y-m-d', $esData['fecha_nacimiento']);
            // Re-formatear evita falsos válidos por desbordamiento (ej. "2023-02-30" → 2023-03-02)
            if (!$fn || $fn->format('Y-m-d') !== $esData['fecha_nacimiento']) {
                $db->transRollback();
                throw new \RuntimeException('La fecha de nacimiento no es válida.', 422);
            }
            $hoy = new \DateTime('today');
            $min = (new \DateTime('today'))->modify('-30 years');
            if ($fn > $hoy) {
                $db->transRollback();
                throw new \RuntimeException('La fecha de nacimiento no puede ser en el futuro.', 422);
            }
            if ($fn < $min) {
                $db->transRollback();
                throw new \RuntimeException('El estudiante no puede tener más de 30 años.', 422);
            }
        }

        $idEstudiante = $this->estudianteModel->insert([
            'id_apoderado'     => $idApoderado,
            'id_promocion'     => $idPromocion,
            'nombres'          => $esData['nombres'],
            'apellidos'        => $esData['apellidos'] ?? null,
            'fecha_nacimiento' => $esData['fecha_nacimiento'] ?? null,
            'color_fav'        => $esData['color_fav']        ?? null,
            'profesion_futura' => $esData['profesion_futura'] ?? null,
        ]);

        if ($idEstudiante === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->estudianteModel->errors()), 422);
        }

        // Asignar productos seleccionados al estudiante
        if (!empty($productosIds)) {
            $rows = array_map(
                fn($idProd) => ['id_estudiante' => $idEstudiante, 'id_producto' => $idProd],
                $productosIds
            );
            $db->table('estudiante_productos')->insertBatch($rows);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al registrar el estudiante', 500);
        }

        return $idEstudiante;
    }

    /**
     * Actualiza los datos propios del estudiante (no del apoderado).
     *
     * Solo modifica los campos presentes en $data.
     *
     * @param  int                  $id   ID del estudiante.
     * @param  array<string, mixed> $data Campos a actualizar (parcial).
     * @return void
     *
     * @throws \RuntimeException 404 si el estudiante no existe.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     */
    public function actualizar(int $id, array $data): void
    {
        if (!$this->estudianteModel->find($id)) {
            throw new \RuntimeException('Estudiante no encontrado', 404);
        }

        $upper = function ($v) {
            if ($v === null) return null;
            $v = trim((string) $v);
            return mb_strtoupper($v, 'UTF-8');
        };

        $update = array_filter([
            'nombres'          => isset($data['nombres']) ? $upper($data['nombres']) : null,
            'apellidos'        => isset($data['apellidos']) ? $upper($data['apellidos']) : null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'color_fav'        => $data['color_fav']        ?? null,
            'profesion_futura' => isset($data['profesion_futura']) ? $upper($data['profesion_futura']) : null,
        ], fn($v) => $v !== null);

        if (!empty($update) && $this->estudianteModel->update($id, $update) === false) {
            throw new \RuntimeException(json_encode($this->estudianteModel->errors()), 422);
        }
    }

    /**
     * Elimina un estudiante. La asistencia asociada se elimina en cascada por FK.
     *
     * @param  int  $id ID del estudiante.
     * @return void
     *
     * @throws \RuntimeException 404 si el estudiante no existe.
     */
    public function eliminar(int $id): void
    {
        if (!$this->estudianteModel->find($id)) {
            throw new \RuntimeException('Estudiante no encontrado', 404);
        }
        $this->estudianteModel->delete($id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMPORTACIÓN / EXPORTACIÓN CSV
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna todos los estudiantes de una promoción en formato aplanado
     * (incluye datos del apoderado y correo) listo para volcar a CSV.
     *
     * @param  int $idPromocion
     * @return array<int, array<string, mixed>>
     */
    public function exportarDatos(int $idPromocion): array
    {
        $db = \Config\Database::connect();

        return $db->query(
            'SELECT
                e.nombres, e.apellidos, e.fecha_nacimiento, e.color_fav, e.profesion_futura,
                p.nombres  AS apoderado_nombres, p.apellidos AS apoderado_apellidos,
                p.telefono AS apoderado_telefono, p.correo   AS apoderado_correo,
                a.tipo_relacion
             FROM estudiantes e
             JOIN apoderados a ON a.id_apoderado = e.id_apoderado
             JOIN personas   p ON p.id_persona   = a.id_persona
             WHERE e.id_promocion = ?
             ORDER BY e.apellidos ASC, e.nombres ASC',
            [$idPromocion]
        )->getResultArray();
    }

    /**
     * Importa estudiantes desde un array de filas ya parseadas del CSV.
     * Reutiliza crear() para mantener todas las validaciones y transacciones ACID.
     *
     * Columnas esperadas:
     *   nombres*, apellidos, fecha_nacimiento (YYYY-MM-DD), color_favorito,
     *   profesion_futura, apoderado_nombres*, apoderado_apellidos,
     *   apoderado_telefono* (9 dígitos), apoderado_correo, tipo_relacion*.
     *
     * @param  int   $idPromocion
     * @param  array $filas  Array de arrays asociativos con las columnas del CSV.
     * @return array{ creados: int, errores: list<array{fila: int, mensaje: string}> }
     */
    public function importarDesdeArray(int $idPromocion, array $filas): array
    {
        $creados = 0;
        $errores = [];

        foreach ($filas as $i => $fila) {
            $num          = $i + 2; // fila 1 = encabezado
            $nombres      = trim($fila['nombres']             ?? '');
            $apNombres    = trim($fila['apoderado_nombres']   ?? '');
            $apTelefono   = trim($fila['apoderado_telefono']  ?? '');
            $tipoRelacion = strtolower(trim($fila['tipo_relacion'] ?? ''));

            if ($nombres === '') {
                $errores[] = ['fila' => $num, 'mensaje' => 'El campo "nombres" es obligatorio.'];
                continue;
            }
            if ($apNombres === '') {
                $errores[] = ['fila' => $num, 'mensaje' => 'El campo "apoderado_nombres" es obligatorio.'];
                continue;
            }
            if (!preg_match('/^\d{9}$/', $apTelefono)) {
                $errores[] = ['fila' => $num, 'mensaje' => '"apoderado_telefono" debe tener exactamente 9 dígitos.'];
                continue;
            }
            if (!in_array($tipoRelacion, ['padre', 'madre', 'hermano', 'otro'], true)) {
                $errores[] = ['fila' => $num, 'mensaje' => '"tipo_relacion" debe ser padre, madre, hermano u otro.'];
                continue;
            }

            $fechaNac = trim($fila['fecha_nacimiento'] ?? '');
            if ($fechaNac !== '') {
                $fn = \DateTime::createFromFormat('Y-m-d', $fechaNac);
                if (!$fn || $fn->format('Y-m-d') !== $fechaNac) {
                    $errores[] = ['fila' => $num, 'mensaje' => '"fecha_nacimiento" debe estar en formato YYYY-MM-DD (ej. 2005-03-15).'];
                    continue;
                }
            }

            try {
                $this->crear([
                    'id_promocion' => $idPromocion,
                    'productos'    => [],
                    'estudiante'   => [
                        'nombres'          => $nombres,
                        'apellidos'        => trim($fila['apellidos']        ?? '') ?: null,
                        'fecha_nacimiento' => $fechaNac                                ?: null,
                        'color_fav'        => trim($fila['color_favorito']   ?? '') ?: null,
                        'profesion_futura' => trim($fila['profesion_futura'] ?? '') ?: null,
                    ],
                    'apoderado'    => [
                        'nombres'       => $apNombres,
                        'apellidos'     => trim($fila['apoderado_apellidos'] ?? '') ?: null,
                        'telefono'      => $apTelefono,
                        'correo'        => trim($fila['apoderado_correo']    ?? '') ?: null,
                        'tipo_relacion' => $tipoRelacion,
                    ],
                ]);
                $creados++;
            } catch (\RuntimeException $e) {
                $errores[] = ['fila' => $num, 'mensaje' => $e->getMessage()];
            }
        }

        return ['creados' => $creados, 'errores' => $errores];
    }
}
