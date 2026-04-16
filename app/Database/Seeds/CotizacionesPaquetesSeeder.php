<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CotizacionesPaquetesSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('cotizaciones_paquetes')->insert([
            'id_cotizacion'=>1,
            'id_paquete'=>2
        ]);
    }
}
