<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContratosSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('contratos')->insertBatch([
             [
                'id_cotizacion'     => 1, // Boda Mendoza
                'adelanto'          => 625.00, // 25% del total
                'total_final'       => 2500.00,
                'fecha_contrato'    => '2025-09-05',
                'fecha_emision'     => '2025-09-05',
                'fecha_hora_inicio' => '2025-12-20 16:00:00',
                'fecha_hora_fin'    => '2025-12-21 02:00:00',
                'observaciones'     => 'Cliente pagó adelanto en efectivo. Saldo en 2 cuotas.',
                'estado'            => 'ACTIVO',
            ],
            [
                'id_cotizacion'     => 2, // Anuario Escolar
                'adelanto'          => 3000.00, // 20% del total
                'total_final'       => 15000.00,
                'fecha_contrato'    => '2025-08-20',
                'fecha_emision'     => '2025-08-20',
                'fecha_hora_inicio' => '2025-10-10 08:00:00',
                'fecha_hora_fin'    => '2025-10-10 17:00:00',
                'observaciones'     => 'Pago del saldo contraentrega de los anuarios.',
                'estado'            => 'ACTIVO',
            ],
        ]);
    }
}
