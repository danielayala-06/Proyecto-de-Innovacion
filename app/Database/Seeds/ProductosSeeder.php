<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('productos')->insertBatch([
            ['descripcion'=>'Álbum','precio_referencial'=>150, 'tamanio'=>'10x25'],
            ['descripcion'=>'CUADRO','precio_referencial'=>110, 'tamanio'=>'50x30'],
        ]);
    }
}
