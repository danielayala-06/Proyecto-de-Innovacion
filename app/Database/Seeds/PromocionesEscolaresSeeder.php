<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PromocionesEscolaresSeeder extends Seeder
{
    public function run()
    {
        // Una promoción por cotización; grado usa el ENUM del modelo.
        $data = [
            [
                'id_colegio'      => 1,
                'id_cotizacion'   => 1,
                'nombre'          => 'Promoción 2026 – 5to A',
                'grado'           => 'Secundaria',
                'seccion'         => 'A',
                'num_estudiantes' => 30,
                'anio'            => 2026,
                'is_active'       => true,
            ],
            [
                'id_colegio'      => 1,
                'id_cotizacion'   => 1,
                'nombre'          => 'Promoción 2026 – 5to B',
                'grado'           => 'Secundaria',
                'seccion'         => 'B',
                'num_estudiantes' => 28,
                'anio'            => 2026,
                'is_active'       => true,
            ],
            [
                'id_colegio'      => 2,
                'id_cotizacion'   => 2,
                'nombre'          => 'Promoción 2026 – 6to Primaria',
                'grado'           => 'Primaria',
                'seccion'         => 'A',
                'num_estudiantes' => 35,
                'anio'            => 2026,
                'is_active'       => true,
            ],
            [
                'id_colegio'      => 3,
                'id_cotizacion'   => 3,
                'nombre'          => 'Promoción 2026 – Inicial A',
                'grado'           => 'Inicial',
                'seccion'         => 'A',
                'num_estudiantes' => 25,
                'anio'            => 2026,
                'is_active'       => true,
            ],
        ];

        $this->db->table('promociones_escolares')->insertBatch($data);
    }
}
