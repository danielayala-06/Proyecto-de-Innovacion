<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesProductosSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('paquetes_productos')->insertBatch([
             ['id_paquete' => 1, 'id_producto' => 2, 'cantidad' => 1],
             ['id_paquete' => 2, 'id_producto' => 1, 'cantidad' => 1],
             ['id_paquete' => 2, 'id_producto' => 2, 'cantidad' => 1],
             ['id_paquete' => 3, 'id_producto' => 3, 'cantidad' => 1],
             ['id_paquete' => 4, 'id_producto' => 2, 'cantidad' => 1],
        ]);
    }
}
