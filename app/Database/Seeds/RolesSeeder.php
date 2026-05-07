<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['rol' => 'Administrador', 'estado' => 'ACTIVO'],
            ['rol' => 'Vendedor',      'estado' => 'ACTIVO'],
            ['rol' => 'Fotógrafo',     'estado' => 'ACTIVO'],
            ['rol' => 'Supervisor',    'estado' => 'ACTIVO'],
        ];

        $this->db->table('roles')->insertBatch($data);
    }
}
