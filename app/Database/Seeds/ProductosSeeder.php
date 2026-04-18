<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('productos')->insertBatch([
            [
                'nombre'=>'Album rojo Mediano',
                'descripcion'=>'Álbum rojo tamanio mediano',
                'precio_referencial'=>150,
                'tamanio'=>'10x25'
            ],
            [
                'nombre'=>'Cuadro laminado grande',
                'descripcion'=>'Cuadro laminado grande con extension wifi',
                'precio_referencial'=>110,
                'tamanio'=>'50x30'
            ],
            [
                'nombre'=>'Libro Virtual',
                'descripcion'=>'Libro virtual estatico con 5 fotografias',
                'precio_referencial'=>20,
                'tamanio'=>null,
            ],
            [
                'nombre'=>'USB pack promo',
                'descripcion'=>'USB con 50 fotografias y 12 videos.',
                'precio_referencial'=>40,
                'tamanio'=>null,
            ],
            [
                'nombre'=>'Anuario',
                'descripcion'=>'Anuario basico',
                'precio_referencial'=>90,
                'tamanio'=>'30x20',
            ],
        ]);
    }
}
