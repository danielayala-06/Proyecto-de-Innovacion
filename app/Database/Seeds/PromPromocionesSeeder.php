<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PromPromocionesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'colegio_id'      => 1,
                'nombre'          => 'Promoción 2026 — 5.° A',
                'nivel'           => 'Secundaria',
                'cuadros_total'   => 15,
                'cuadros_usados'  => 0,
                'anuarios_total'  => 15,
                'anuarios_usados' => 0,
                'activa'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('prom_promociones')->insertBatch($data);
    }
}
