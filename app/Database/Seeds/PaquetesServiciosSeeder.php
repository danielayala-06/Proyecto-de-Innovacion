<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesServiciosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('paquetes_servicios')->insertBatch([
            ['id_servicio'=>1,'id_paquete'=>1],
            ['id_servicio'=>1,'id_paquete'=>2],
            ['id_servicio'=>2,'id_paquete'=>2]
        ]);
    }
}
