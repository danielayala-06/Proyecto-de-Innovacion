<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CotizacionesDetallesSeeder extends Seeder
{
    public function run()
    {
        $pId = fn(string $n) => (int)($this->db->table('paquetes')
            ->where('nombre_paquete', $n)->get()->getRowArray()['id_paquete'] ?? 0);
        $prId = fn(string $n) => (int)($this->db->table('productos')
            ->where('nombre_producto', $n)->get()->getRowArray()['id_producto'] ?? 0);

        $data = [
            // Cotización 1 – Anuario Big Primaria × 30 alumnos
            [
                'id_cotizacion'   => 1,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $pId('Anuario Big Primaria'),
                'descripcion'     => 'Anuario Big Primaria para 30 alumnos de 5to de Secundaria',
                'cantidad'        => 30,
                'precio_unitario' => 150.00,
            ],
            // Cotización 2 – Anuario Medium Primaria × 30 alumnos
            [
                'id_cotizacion'   => 2,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $pId('Anuario Medium Primaria'),
                'descripcion'     => 'Anuario Medium Primaria para 30 alumnos de 6to de Primaria',
                'cantidad'        => 30,
                'precio_unitario' => 145.00,
            ],
            // Cotización 3 – Anuario Small × 25 alumnos
            [
                'id_cotizacion'   => 3,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $pId('Anuario Small'),
                'descripcion'     => 'Anuario Small para 25 alumnos de Inicial',
                'cantidad'        => 25,
                'precio_unitario' => 135.00,
            ],
            // Cotización 3 – Cuadro Maravillas del Mundo adicional × 5
            [
                'id_cotizacion'   => 3,
                'tipo_item'       => 'producto',
                'id_referencia'   => $prId('Cuadro Maravillas del Mundo'),
                'descripcion'     => 'Cuadro Maravillas del Mundo adicional',
                'cantidad'        => 5,
                'precio_unitario' => 110.00,
            ],
            // Cotización 4 – Anuario Big Premium Primaria × 10 alumnos
            [
                'id_cotizacion'   => 4,
                'tipo_item'       => 'paquete',
                'id_referencia'   => $pId('Anuario Big Premium Primaria'),
                'descripcion'     => 'Anuario Big Premium Primaria para 10 alumnos de Secundaria',
                'cantidad'        => 10,
                'precio_unitario' => 210.00,
            ],
        ];

        $this->db->table('cotizaciones_detalles')->insertBatch($data);
    }
}
