<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('productos')->insertBatch([
            ['nombre'=>'Album rojo Mediano', 'descripcion'=>'Álbum rojo tamanio mediano','precio_referencial'=>150, 'tamanio'=>'10x25'],
            ['nombre'=>'Cuadro laminado grande', 'descripcion'=>'Cuadro laminado grande con extension wifi','precio_referencial'=>110, 'tamanio'=>'50x30'],
        ]);
    }
}
