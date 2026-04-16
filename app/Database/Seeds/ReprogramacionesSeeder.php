<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReprogramacionesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('reprogramaciones')->insert([
            'fecha_anterior'=>'2026-05-01 10:00:00',
            'fecha_nueva'=>'2026-05-02 10:00:00',
            'motivo'=>'Clima',
            'fecha_cambio'=>date('Y-m-d H:i:s'),
            'id_contrato'=>1
        ]);
    }
}
