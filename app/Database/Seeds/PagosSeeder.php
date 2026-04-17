<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PagosSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('pagos')->insert([
            'fecha'=>date('Y-m-d'),
            'monto'=>600,
            'moneda'=>'PEN',
            'id_form_pago'=>1,
            'id_contrato'=>1
        ]);
    }
}
