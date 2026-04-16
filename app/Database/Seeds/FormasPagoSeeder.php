<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FormasPagoSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('formas_pago')->insertBatch([
            ['forma_pago'=>'EFECTIVO','tipo_pago'=>'CONTADO'],
            ['forma_pago'=>'TRANSFERENCIA','tipo_pago'=>'YAPE'],
        ]);
    }
}
