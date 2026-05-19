<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PagosSeeder extends Seeder
{
    public function run()
    {
        // El adelanto inicial ya está registrado en contratos.adelanto y se suma
        // automáticamente en ContratoService al calcular totalPagado.
        // Esta tabla solo contiene ABONOS POSTERIORES al adelanto.
        //
        // Contrato 1: total=13 500, adelanto=5 000 → saldo base 8 500
        //   + pago 4 000 → saldo pendiente 4 500
        // Contrato 2: total=4 200, adelanto=1 500 → saldo base 2 700
        //   + pago 1 300 → saldo pendiente 1 400
        $data = [
            [
                'fecha'        => '2026-05-01',
                'monto'        => 4000.00,
                'moneda'       => 'PEN',
                'voucher'      => 'BCP-2026050198765',
                'id_form_pago' => 2,
                'id_contrato'  => 1,
            ],
            [
                'fecha'        => '2026-05-10',
                'monto'        => 1300.00,
                'moneda'       => 'PEN',
                'voucher'      => 'IBK-20260510-112233',
                'id_form_pago' => 3,
                'id_contrato'  => 2,
            ],
        ];

        $this->db->table('pagos')->insertBatch($data);
    }
}
