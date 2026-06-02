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

    public function todasConColegio(): array
    {
        return $this->select('prom_promociones.*, colegios.nombre_colegio')
            ->join('colegios', 'colegios.id_colegio = prom_promociones.colegio_id', 'left')
            ->orderBy('prom_promociones.created_at', 'DESC')
            ->findAll();
    }
}
