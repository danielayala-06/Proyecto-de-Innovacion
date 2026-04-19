<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PromocionesSeeder extends Seeder
{
    public function run()
    {
        $promociones = [
            [
                'nombre_promocion' => 'Promo Fiestas Patrias',
                'tipo'             => 'DESCUENTO',
                'fecha_inicio'     => '2025-07-01',
                'fecha_fin'        => '2025-07-31',
                'descuento'        => 15.00,
                'estado'           => 'VENCIDA',
            ],
            [
                'nombre_promocion' => 'Promo Día de la Madre',
                'tipo'             => 'DESCUENTO',
                'fecha_inicio'     => '2025-05-01',
                'fecha_fin'        => '2025-05-12',
                'descuento'        => 20.00,
                'estado'           => 'VENCIDA',
            ],
            [
                'nombre_promocion' => 'Promo Aniversario',
                'tipo'             => 'DESCUENTO',
                'fecha_inicio'     => '2025-10-01',
                'fecha_fin'        => '2025-10-31',
                'descuento'        => 10.00,
                'estado'           => 'ACTIVO',
            ],
            [
                'nombre_promocion' => 'Promo Navidad',
                'tipo'             => 'DESCUENTO',
                'fecha_inicio'     => '2025-12-01',
                'fecha_fin'        => '2025-12-31',
                'descuento'        => 25.00,
                'estado'           => 'ACTIVO',
            ],
        ];

        $this->db->table('promociones')->insertBatch($promociones);

        // Tabla grande: solo 2 registros por tabla intermedia
        $this->db->table('promociones_paquetes')->insertBatch([
            ['id_promocion' => 1, 'id_paquete' => 2], // Fiestas Patrias → Bodas Gold
            ['id_promocion' => 3, 'id_paquete' => 1], // Aniversario → Básico
        ]);

        $this->db->table('promociones_servicios')->insertBatch([
            ['id_promocion' => 2, 'id_servicio' => 1], // Día de la Madre → Fotografía eventos
            ['id_promocion' => 4, 'id_servicio' => 2], // Navidad → Video cinematográfico
        ]);

        $this->db->table('promociones_productos')->insertBatch([
            ['id_promocion' => 1, 'id_producto' => 1], // Fiestas Patrias → Álbum premium
            ['id_promocion' => 4, 'id_producto' => 4], // Navidad → Fotolibro mediano
        ]);
    }
}
