<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PromocionesEscolaresSeeder extends Seeder
{
    public function run()
    {
        // Colegios: 1-4, Cotizaciones: 1 y 3 (solo las APROBADAS)
        $data = [
            [
                'id_colegio'     => 1,
                'id_cotizacion'  => 1,
                'nombre'         => 'Promoción 2026 – 5to A',
                'grado'          => '5to',
                'seccion'        => 'A',
                'num_estudiantes'=> 30,
                'anio'           => 2026,
                'is_active'      => true,
            ],
            [
                'id_colegio'     => 1,
                'id_cotizacion'  => 1,
                'nombre'         => 'Promoción 2026 – 5to B',
                'grado'          => '5to',
                'seccion'        => 'B',
                'num_estudiantes'=> 28,
                'anio'           => 2026,
                'is_active'      => true,
            ],
            [
                'id_colegio'     => 3,
                'id_cotizacion'  => 3,
                'nombre'         => 'Promoción 2026 – 6to Primaria',
                'grado'          => '6to',
                'seccion'        => 'A',
                'num_estudiantes'=> 25,
                'anio'           => 2026,
                'is_active'      => true,
            ],
            [
                'id_colegio'     => 2,
                'id_cotizacion'  => 3,
                'nombre'         => 'Inicial 5 años 2026',
                'grado'          => '5 años',
                'seccion'        => null,
                'num_estudiantes'=> 20,
                'anio'           => 2026,
                'is_active'      => false,
            ],
        ];

        $this->db->table('promociones_escolares')->insertBatch($data);
    }
}
