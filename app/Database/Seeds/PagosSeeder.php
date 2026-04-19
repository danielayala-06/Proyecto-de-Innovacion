<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PagosSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('pagos')->insertBatch([
             [
                 'id_contrato'  => 1,
                 'id_form_pago' => 1, // Efectivo (adelanto ya registrado en contrato)
                 'monto'        => 625.00,
                 'moneda'       => 'PEN',
                 'voucher'      => null,
                 'fecha'        => '2025-09-05',
                 'estado'       => 'COMPLETADO',
             ],
             [
                 'id_contrato'  => 1,
                 'id_form_pago' => 2, // Transferencia
                 'monto'        => 937.50,
                 'moneda'       => 'PEN',
                 'voucher'      => 'TRF-20251001-001',
                 'fecha'        => '2025-10-01',
                 'estado'       => 'COMPLETADO',
             ],
             [
                 'id_contrato'  => 2,
                 'id_form_pago' => 3, // Yape
                 'monto'        => 3000.00,
                 'moneda'       => 'PEN',
                 'voucher'      => 'YAPE-20250820-334',
                 'fecha'        => '2025-08-20',
                 'estado'       => 'COMPLETADO',
             ],
             [
                 'id_contrato'  => 2,
                 'id_form_pago' => 2, // Transferencia
                 'monto'        => 6000.00,
                 'moneda'       => 'PEN',
                 'voucher'      => 'TRF-20250901-112',
                 'fecha'        => '2025-09-01',
                 'estado'       => 'COMPLETADO',
             ],
         ]);
    }
}
