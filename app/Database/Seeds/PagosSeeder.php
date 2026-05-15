<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PagosSeeder extends Seeder
{
    public function run()
    {
        // Contratos: 1-4, FormasPago: 1=Efectivo, 2=BCP, 3=Interbank, 4=Yape, 5=Plin
        $data = [
            // Pagos del Contrato 1 (total 13500, adelanto 5000)
            [
                'fecha'       => '2026-03-12',
                'monto'       => 5000.00,
                'moneda'      => 'PEN',
                'voucher'     => null,
                'id_form_pago'=> 1,
                'id_contrato' => 1,
            ],
            [
                'fecha'       => '2026-05-01',
                'monto'       => 4000.00,
                'moneda'      => 'PEN',
                'voucher'     => 'BCP-2026050198765',
                'id_form_pago'=> 2,
                'id_contrato' => 1,
            ],
            // Pagos del Contrato 2 (total 6000, adelanto 2000)
            [
                'fecha'       => '2026-04-05',
                'monto'       => 2000.00,
                'moneda'      => 'PEN',
                'voucher'     => null,
                'id_form_pago'=> 4,
                'id_contrato' => 2,
            ],
            [
                'fecha'       => '2026-05-05',
                'monto'       => 2000.00,
                'moneda'      => 'PEN',
                'voucher'     => 'IBK-20260505-112233',
                'id_form_pago'=> 3,
                'id_contrato' => 2,
            ],
        ];

        $this->db->table('pagos')->insertBatch($data);
    }
}
