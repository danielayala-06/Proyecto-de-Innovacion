<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ApoderadosSeeder extends Seeder
{
    public function run()
    {
        // Reutilizamos las personas que son clientes (5-8) como apoderados
        $data = [
            [
                'id_persona'   => 5,
                'tipo_relacion'=> 'padre',
            ],
            [
                'id_persona'   => 6,
                'tipo_relacion'=> 'madre',
            ],
            [
                'id_persona'   => 7,
                'tipo_relacion'=> 'madre',
            ],
            [
                'id_persona'   => 8,
                'tipo_relacion'=> 'otro',
            ],
        ];

        $this->db->table('apoderados')->insertBatch($data);
    }
}
