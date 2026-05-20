<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para la tabla `reglas_paquetes`.
 * Define condiciones y beneficios aplicables a grupos de paquetes/productos.
 *
 * La asociación paquete/producto→regla es many-to-many y vive en `reglas_items`.
 * Esta tabla solo guarda la definición de la regla (condición + beneficio).
 *
 * Tipos de condición: CANTIDAD_MIN | CANTIDAD_MAX.
 * Tipos de beneficio: producto_gratis | sesion_unica | otro.
 */
class ReglasPaquetesModel extends Model
{
    protected $table            = 'reglas_paquetes';
    protected $primaryKey       = 'id_regla';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'descripcion',
        'tipo_condicion',
        'valor_condicion',
        'tipo_beneficio',
        'id_producto_beneficio',
        'valor_beneficio',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $skipValidation         = false;
    protected $cleanValidationRules   = true;

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS PÚBLICAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna todas las reglas asociadas a un paquete (via reglas_items),
     * incluyendo los ítems (paquetes/productos) de cada regla.
     *
     * @param  int   $idPaquete
     * @return array
     */
    public function reglasParaPaquete(int $idPaquete): array
    {
        $rows = $this->db->query(
            $this->_sqlBaseReglas() . "
            WHERE r.id_regla IN (
                SELECT id_regla FROM reglas_items
                WHERE tipo_item = 'paquete' AND id_referencia = ?
            )
            ORDER BY r.id_regla, ri.tipo_item, ri.id_referencia
            ",
            [$idPaquete]
        )->getResultArray();

        return $this->_agruparConItems($rows);
    }

    /**
     * Evalúa las reglas aplicables a una lista de detalles de cotización.
     *
     *  - CANTIDAD_MAX superada → violación.
     *  - CANTIDAD_MIN cumplida → activada (beneficio disponible).
     *
     * @param  array $detalles  Filas con tipo_item, id_referencia, cantidad.
     * @return array{ violaciones: array, activadas: array }
     */
    public function evaluarDetalles(array $detalles): array
    {
        ['paquetes' => $porPaquete, 'productos' => $porProducto] =
            $this->_indexarDetalles($detalles);

        if (empty($porPaquete) && empty($porProducto)) {
            return ['violaciones' => [], 'activadas' => []];
        }

        $rows      = $this->_consultarReglasParaItems($porPaquete, $porProducto);
        $reglasMap = $this->_acumularCantidades($rows, $porPaquete, $porProducto);

        return $this->_clasificarReglas($reglasMap);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fragmento SQL reutilizable: SELECT + JOINs de reglas con su producto beneficio.
     */
    private function _sqlBaseReglas(): string
    {
        return "
            SELECT r.id_regla, r.descripcion, r.tipo_condicion, r.valor_condicion,
                   r.tipo_beneficio, r.id_producto_beneficio, r.valor_beneficio,
                   pb.nombre_producto AS nombre_producto_beneficio,
                   ri.tipo_item, ri.id_referencia
            FROM reglas_paquetes r
            LEFT JOIN productos pb ON pb.id_producto = r.id_producto_beneficio
            JOIN reglas_items ri ON ri.id_regla = r.id_regla
        ";
    }

    /**
     * Separa los detalles en dos mapas id→cantidad: uno para paquetes y otro para productos.
     *
     * @return array{ paquetes: array<int,int>, productos: array<int,int> }
     */
    private function _indexarDetalles(array $detalles): array
    {
        $paquetes  = [];
        $productos = [];

        foreach ($detalles as $d) {
            $tipo = strtolower($d['tipo_item'] ?? '');
            $id   = (int) ($d['id_referencia'] ?? 0);
            if ($id === 0) continue;

            if ($tipo === 'paquete') {
                $paquetes[$id]  = ($paquetes[$id]  ?? 0) + (int) $d['cantidad'];
            } elseif ($tipo === 'producto') {
                $productos[$id] = ($productos[$id] ?? 0) + (int) $d['cantidad'];
            }
        }

        return ['paquetes' => $paquetes, 'productos' => $productos];
    }

    /**
     * Consulta todas las reglas que aplican al conjunto de paquetes/productos recibidos.
     * Los IDs se castean explícitamente a int para evitar inyección.
     */
    private function _consultarReglasParaItems(array $porPaquete, array $porProducto): array
    {
        $condiciones = [];

        if (!empty($porPaquete)) {
            $ids           = implode(',', array_map('intval', array_keys($porPaquete)));
            $condiciones[] = "(ri.tipo_item = 'paquete' AND ri.id_referencia IN ({$ids}))";
        }

        if (!empty($porProducto)) {
            $ids           = implode(',', array_map('intval', array_keys($porProducto)));
            $condiciones[] = "(ri.tipo_item = 'producto' AND ri.id_referencia IN ({$ids}))";
        }

        return $this->db->query(
            $this->_sqlBaseReglas() . 'WHERE ' . implode(' OR ', $condiciones)
        )->getResultArray();
    }

    /**
     * Acumula, por regla, la cantidad total de todos sus ítems presentes en los detalles.
     *
     * @return array<int, array> Mapa id_regla → datos de la regla con cantidad_total.
     */
    private function _acumularCantidades(array $rows, array $porPaquete, array $porProducto): array
    {
        $reglasMap = [];

        foreach ($rows as $row) {
            $idRegla = (int) $row['id_regla'];

            if (!isset($reglasMap[$idRegla])) {
                $reglasMap[$idRegla] = $this->_mapearRegla($row);
            }

            $tipo = $row['tipo_item'];
            $id   = (int) $row['id_referencia'];
            $reglasMap[$idRegla]['cantidad_total'] +=
                $tipo === 'paquete' ? ($porPaquete[$id] ?? 0) : ($porProducto[$id] ?? 0);
        }

        return $reglasMap;
    }

    /**
     * Clasifica reglas en violaciones (CANTIDAD_MAX superada) y activadas (CANTIDAD_MIN cumplida).
     *
     * @return array{ violaciones: array, activadas: array }
     */
    private function _clasificarReglas(array $reglasMap): array
    {
        $violaciones = [];
        $activadas   = [];

        foreach ($reglasMap as $r) {
            $cant   = $r['cantidad_total'];
            $umbral = $r['valor_condicion'];

            if ($r['tipo_condicion'] === 'CANTIDAD_MAX' && $cant > $umbral) {
                $violaciones[] = [
                    'descripcion' => $r['descripcion'],
                    'cantidad'    => $cant,
                    'limite'      => (int) $umbral,
                ];
            } elseif ($r['tipo_condicion'] === 'CANTIDAD_MIN' && $cant >= $umbral) {
                $activadas[] = [
                    'tipo_beneficio'            => $r['tipo_beneficio'],
                    'id_producto_beneficio'     => $r['id_producto_beneficio'],
                    'nombre_producto_beneficio' => $r['nombre_producto_beneficio'],
                    'valor_beneficio'           => $r['valor_beneficio'],
                    'descripcion'               => $r['descripcion'],
                ];
            }
        }

        return ['violaciones' => $violaciones, 'activadas' => $activadas];
    }

    /**
     * Construye el array base compartido de una regla a partir de una fila del JOIN.
     * Los campos comunes entre reglasParaPaquete() y evaluarDetalles() están aquí.
     */
    private function _mapearReglaBase(array $row): array
    {
        return [
            'descripcion'               => $row['descripcion'],
            'tipo_condicion'            => $row['tipo_condicion'],
            'valor_condicion'           => (float) $row['valor_condicion'],
            'tipo_beneficio'            => $row['tipo_beneficio'],
            'id_producto_beneficio'     => $row['id_producto_beneficio'] ? (int) $row['id_producto_beneficio'] : null,
            'nombre_producto_beneficio' => $row['nombre_producto_beneficio'] ?? null,
            'valor_beneficio'           => $row['valor_beneficio'],
        ];
    }

    /**
     * Regla para evaluarDetalles: incluye cantidad_total para la acumulación.
     */
    private function _mapearRegla(array $row): array
    {
        return array_merge($this->_mapearReglaBase($row), ['cantidad_total' => 0]);
    }

    /**
     * Agrupa filas del JOIN (una por ítem) en reglas con array de ítems anidados.
     */
    private function _agruparConItems(array $rows): array
    {
        $reglas = [];

        foreach ($rows as $row) {
            $id = (int) $row['id_regla'];

            if (!isset($reglas[$id])) {
                $reglas[$id] = array_merge(
                    ['id_regla' => $id],
                    $this->_mapearReglaBase($row),
                    ['items' => []]
                );
            }

            $reglas[$id]['items'][] = [
                'tipo_item'     => $row['tipo_item'],
                'id_referencia' => (int) $row['id_referencia'],
            ];
        }

        return array_values($reglas);
    }
}
