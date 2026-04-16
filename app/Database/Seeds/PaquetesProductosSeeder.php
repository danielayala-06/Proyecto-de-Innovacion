<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesProductosSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('paquetes_productos')->insertBatch([
            ['id_producto'=>1,'id_paquete'=>2],
            ['id_producto'=>2,'id_paquete'=>2]
        ]);
    }
}
