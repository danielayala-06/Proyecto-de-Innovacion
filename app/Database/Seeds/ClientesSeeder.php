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
            ],
            [
                'red_social'=>'instagram.com/morgan',
                'id_persona'=>3,
                'id_empresa'=>null
            ],
            [
                'red_social'=>'instagram.com/digtyT',
                'id_persona'=>4,
                'id_empresa'=>null
            ],
            [
                'red_social'=>'instagram.com/arellaT',
                'id_persona'=>8,
                'id_empresa'=>null
            ],
        ]);
    }
}
