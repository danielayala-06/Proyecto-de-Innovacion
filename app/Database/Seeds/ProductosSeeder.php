<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('productos')->insertBatch([
            ['descripcion'=>'Álbum','precio_referencial'=>150],
            ['descripcion'=>'Cuadro','precio_referencial'=>80]
        ]);
    }
}
