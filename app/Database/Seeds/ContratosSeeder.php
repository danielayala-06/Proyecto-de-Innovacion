<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContratosSeeder extends Seeder
{
    public function run()
    {
        // Solo cotizaciones APROBADAS generan contrato: 1 y 3
        $data = [
            [
                'id_cotizacion' => 1,
                'fecha_creacion'=> '2026-03-12',
                'fecha_emision' => '2026-03-15',
                'adelanto'      => 5000.00,
                'total'         => 13500.00,
                'observaciones' => 'Adelanto del 37% pagado en efectivo. Saldo a pagar antes de entrega.',
                'estado'        => 'ACTIVO',
            ],
            [
                'id_cotizacion' => 3,
                'fecha_creacion'=> '2026-04-05',
                'fecha_emision' => '2026-04-08',
                'adelanto'      => 2000.00,
                'total'         => 6000.00,
                'observaciones' => 'Pago en dos cuotas acordado con el cliente.',
                'estado'        => 'ACTIVO',
            ],
        ];

        $this->db->table('contratos')->insertBatch($data);
    }
}
