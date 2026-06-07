<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FormasPagoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nombre_forma_pago' => 'Efectivo', 'tipo_pago' => 'EFECTIVO'],
            ['nombre_forma_pago' => 'Yape',     'tipo_pago' => 'OTRO'],
            ['nombre_forma_pago' => 'Plin',     'tipo_pago' => 'OTRO'],
        ];

        $this->db->table('formas_pago')->insertBatch($data);
    }
}
