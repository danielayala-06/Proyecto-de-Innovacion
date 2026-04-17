<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PersonasSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('personas')->insertBatch([
            [
                'nombres'=>'Juan',
                'apellidos'=>'Perez',
                'telefono'=>'999111222',
                'correo'=>'juan@test.com',
                'numero_documento'=>'12345678',
                'tipo_documento'=>'DNI'
            ],
            [
                'nombres'=>'Maria',
                'apellidos'=>'Lopez',
                'telefono'=>'988777666',
                'correo'=>'maria@test.com',
                'numero_documento'=>'87654321',
                'tipo_documento'=>'DNI'
            ]
        ]);
    }
}
