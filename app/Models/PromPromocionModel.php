<?php

namespace App\Models;

use CodeIgniter\Model;

class PromPromocionModel extends Model
{
    protected $table            = 'prom_promociones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'colegio_id', 'id_promocion_escolar', 'token_compartido', 'nombre', 'nivel',
        'cuadros_total', 'cuadros_usados',
        'anuarios_total', 'anuarios_usados',
        'activa', 'created_at',
    ];

    protected $useTimestamps = false;

    public function stockDisponible(int $id): array
    {
        $row = $this->select('cuadros_total, cuadros_usados, anuarios_total, anuarios_usados')
                    ->find($id);

        if (!$row) {
            return ['cuadros' => 0, 'anuarios' => 0];
        }

        return [
            'cuadros'  => max(0, (int) $row['cuadros_total']  - (int) $row['cuadros_usados']),
            'anuarios' => max(0, (int) $row['anuarios_total'] - (int) $row['anuarios_usados']),
        ];
    }

    public function resumen(int $id): ?array
    {
        $promocion = $this->select('prom_promociones.*, colegios.nombre_colegio')
            ->join('colegios', 'colegios.id_colegio = prom_promociones.colegio_id', 'left')
            ->find($id);

        if (!$promocion) {
            return null;
        }

        $db = \Config\Database::connect();

        $total      = (int) $db->table('prom_alumnos')->where('promocion_id', $id)->countAllResults();
        $completados = (int) $db->table('prom_alumnos')->where('promocion_id', $id)->where('completado', 1)->countAllResults();

        $promocion['total_alumnos'] = $total;
        $promocion['completados']   = $completados;
        $promocion['pendientes']    = $total - $completados;

        return $promocion;
    }

    /**
     * Determina qué productos son seleccionables en el formulario para una promocion_escolar.
     * La selección solo se muestra cuando hay paquetes que dan SOLO cuadros Y otros que dan
     * SOLO anuarios (el padre elige cuál corresponde a su hijo). Si un único paquete incluye
     * ambos productos, no hay elección: el alumno recibe los dos automáticamente.
     */
    public function productosSeleccionables(int $idPromocionEscolar): array
    {
        $db = \Config\Database::connect();

        $rows = $db->query(
            'SELECT
                cd.id_referencia AS id_paquete,
                SUM(CASE WHEN pr.categoria = "cuadro"  THEN pp.cantidad ELSE 0 END) AS cuadros_pp,
                SUM(CASE WHEN pr.categoria = "anuario" THEN pp.cantidad ELSE 0 END) AS anuarios_pp
             FROM cotizaciones_detalles cd
             JOIN paquetes p              ON p.id_paquete   = cd.id_referencia
             JOIN paquetes_productos pp   ON pp.id_paquete  = p.id_paquete
             JOIN productos pr            ON pr.id_producto = pp.id_producto
             JOIN promociones_escolares pe ON pe.id_cotizacion = cd.id_cotizacion
             WHERE pe.id_promocion = ?
               AND cd.tipo_item = "paquete"
               AND pr.categoria IN ("cuadro", "anuario")
             GROUP BY cd.id_referencia',
            [$idPromocionEscolar]
        )->getResultArray();

        $cuadroOnly  = false;
        $anuarioOnly = false;
        $both        = false;

        foreach ($rows as $row) {
            $hasCuadro  = (int) $row['cuadros_pp']  > 0;
            $hasAnuario = (int) $row['anuarios_pp'] > 0;
            if ($hasCuadro  && !$hasAnuario) $cuadroOnly  = true;
            if (!$hasCuadro && $hasAnuario)  $anuarioOnly = true;
            if ($hasCuadro  && $hasAnuario)  $both        = true;
        }

        return [
            'mostrar_seleccion' => $cuadroOnly && $anuarioOnly,
            'tiene_cuadros'     => $cuadroOnly || $both,
            'tiene_anuarios'    => $anuarioOnly || $both,
        ];
    }

    public function todasConColegio(): array
    {
        return $this->select('prom_promociones.*, colegios.nombre_colegio')
            ->join('colegios', 'colegios.id_colegio = prom_promociones.colegio_id', 'left')
            ->orderBy('prom_promociones.created_at', 'DESC')
            ->findAll();
    }
}
