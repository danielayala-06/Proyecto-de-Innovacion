<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('servicios')->insertBatch([
            ['nombre_servicio'=>'FOTOGRAFIA','detalle_servicio'=>'COBERTURA','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'VIDEO','detalle_servicio'=>'GRABACION','estado'=>'ACTIVO']
        ]);
    }
}
