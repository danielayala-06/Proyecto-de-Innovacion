<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre_paquete'   => 'Paquete Inicial Básico',
                'nivel_disponible' => 'inicial',
                'descripcion'      => 'Paquete básico para nidos y jardines. Incluye anuario y cuadro individual.',
                'imagen'           => null,
                'precio'           => 150.00,
                'estado'           => 'ACTIVO',
            ],
            [
                'nombre_paquete'   => 'Paquete Primaria Completo',
                'nivel_disponible' => 'primaria',
                'descripcion'      => 'Paquete completo para primaria. Incluye anuario premium, cuadro individual y photobook.',
                'imagen'           => null,
                'precio'           => 280.00,
                'estado'           => 'ACTIVO',
            ],
            [
                'nombre_paquete'   => 'Paquete Secundaria Premium',
                'nivel_disponible' => 'secundaria',
                'descripcion'      => 'Paquete premium para promociones de 5to secundaria. Todo incluido.',
                'imagen'           => null,
                'precio'           => 450.00,
                'estado'           => 'ACTIVO',
            ],
            [
                'nombre_paquete'   => 'Paquete Secundaria Estándar',
                'nivel_disponible' => 'secundaria',
                'descripcion'      => 'Paquete estándar para promociones. Incluye anuario y cuadro grupal.',
                'imagen'           => null,
                'precio'           => 320.00,
                'estado'           => 'ACTIVO',
            ],
            [
                'nombre_paquete'   => 'Paquete Especial Otro',
                'nivel_disponible' => 'otro',
                'descripcion'      => 'Paquete personalizable para instituciones y eventos especiales.',
                'imagen'           => null,
                'precio'           => 200.00,
                'estado'           => 'INACTIVO',
            ],
        ];

        $this->db->table('paquetes')->insertBatch($data);
    }
}
