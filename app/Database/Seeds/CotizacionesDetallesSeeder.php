<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CotizacionesDetallesSeeder extends Seeder
{
    public function run()
    {
        // Cotizaciones: 1-4
        // id_referencia apunta a id_paquete o id_producto según tipo_item
        $data = [
            // Cotización 1 - Paquete Secundaria Premium x30 alumnos
            [
                'id_cotizacion'  => 1,
                'tipo_item'      => 'paquete',
                'id_referencia'  => 3,
                'descripcion'    => 'Paquete Secundaria Premium para 30 alumnos',
                'cantidad'       => 30,
                'precio_unitario'=> 450.00,
            ],
            // Cotización 2 - Paquete Primaria Completo x30 alumnos
            [
                'id_cotizacion'  => 2,
                'tipo_item'      => 'paquete',
                'id_referencia'  => 2,
                'descripcion'    => 'Paquete Primaria Completo para 30 alumnos',
                'cantidad'       => 30,
                'precio_unitario'=> 280.00,
            ],
            // Cotización 3 - Paquete Inicial Básico x25 alumnos
            [
                'id_cotizacion'  => 3,
                'tipo_item'      => 'paquete',
                'id_referencia'  => 1,
                'descripcion'    => 'Paquete Inicial Básico para 25 alumnos',
                'cantidad'       => 25,
                'precio_unitario'=> 150.00,
            ],
            // Cotización 3 - Cuadro Grupal adicional
            [
                'id_cotizacion'  => 3,
                'tipo_item'      => 'producto',
                'id_referencia'  => 2,
                'descripcion'    => 'Cuadro Grupal 50x70 adicional',
                'cantidad'       => 5,
                'precio_unitario'=> 90.00,
            ],
            // Cotización 4 - Paquete Secundaria Estándar x10 alumnos
            [
                'id_cotizacion'  => 4,
                'tipo_item'      => 'paquete',
                'id_referencia'  => 4,
                'descripcion'    => 'Paquete Secundaria Estándar para 10 alumnos',
                'cantidad'       => 10,
                'precio_unitario'=> 320.00,
            ],
        ];

        $this->db->table('cotizaciones_detalles')->insertBatch($data);
    }
}
