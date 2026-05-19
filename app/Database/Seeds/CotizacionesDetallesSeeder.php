<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CotizacionesDetallesSeeder extends Seeder
{
    public function run()
    {
        // id_referencia apunta a id_paquete o id_producto según tipo_item.
        // Paquetes: 1=Anuario Small(135), 2=Anuario Medium(145), 3=Anuario Big(150), 4=Anuario Big Premium(210)
        // Productos: 1=Cuadro Individual 30x40, 2=Cuadro Grupal 50x70
        $data = [
            // Cotización 1 – Anuario Big × 30 alumnos → 30 × 450 = 13 500
            [
                'id_cotizacion'   => 1,
                'tipo_item'       => 'paquete',
                'id_referencia'   => 3,
                'descripcion'     => 'Anuario Big para 30 alumnos de 5to de Secundaria',
                'cantidad'        => 30,
                'precio_unitario' => 450.00,
            ],
            // Cotización 2 – Anuario Medium × 30 alumnos → 30 × 280 = 8 400
            [
                'id_cotizacion'   => 2,
                'tipo_item'       => 'paquete',
                'id_referencia'   => 2,
                'descripcion'     => 'Anuario Medium para 30 alumnos de 6to de Primaria',
                'cantidad'        => 30,
                'precio_unitario' => 280.00,
            ],
            // Cotización 3 – Anuario Small × 25 alumnos → 25 × 150 = 3 750
            [
                'id_cotizacion'   => 3,
                'tipo_item'       => 'paquete',
                'id_referencia'   => 1,
                'descripcion'     => 'Anuario Small para 25 alumnos de Inicial',
                'cantidad'        => 25,
                'precio_unitario' => 150.00,
            ],
            // Cotización 3 – Cuadro Grupal 50x70 × 5 → 5 × 90 = 450
            [
                'id_cotizacion'   => 3,
                'tipo_item'       => 'producto',
                'id_referencia'   => 2,
                'descripcion'     => 'Cuadro Grupal 50x70 adicional',
                'cantidad'        => 5,
                'precio_unitario' => 90.00,
            ],
            // Cotización 4 – Anuario Big Premium × 10 alumnos → 10 × 320 = 3 200
            [
                'id_cotizacion'   => 4,
                'tipo_item'       => 'paquete',
                'id_referencia'   => 4,
                'descripcion'     => 'Anuario Big Premium para 10 alumnos de Secundaria',
                'cantidad'        => 10,
                'precio_unitario' => 320.00,
            ],
        ];

        $this->db->table('cotizaciones_detalles')->insertBatch($data);
    }
}
