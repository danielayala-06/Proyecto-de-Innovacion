<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClientesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('clientes')->insertBatch([
            [
                'red_social'=>'facebook.com/juan',
                'id_persona'=>1,
                'id_empresa'=>null
            ],
            [
                'red_social'=>'instagram.com/maria',
                'id_persona'=>2,
                'id_empresa'=>1
            ]
        ]);
    }
}
