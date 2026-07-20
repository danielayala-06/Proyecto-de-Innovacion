<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PromPromocionesSeeder extends Seeder
{
    public function run()
    {
        $row = $this->db->table('colegios')
            ->where('nombre_colegio', 'I.E. ALEXANDER VON HUMBOLT')
            ->get()->getRow();
        $idColegio = $row ? (int) $row->id_colegio : 1;

        $data = [
            [
                'colegio_id'      => $idColegio,
                'nombre'          => 'Humboldt Secundaria 2026 — 5.° Año',
                'nivel'           => 'Secundaria',
                'cuadros_total'   => 0,
                'cuadros_usados'  => 0,
                'anuarios_total'  => 37,
                'anuarios_usados' => 0,
                'activa'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('prom_promociones')->insertBatch($data);
    }
}
