<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('roles')->insertBatch([
            ['rol'=>'admin'],
            ['rol'=>'editor'],
        ]);
    }
}
