<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('productos')->insertBatch([
            [
                'nombre_producto'          => 'Álbum fotográfico premium',
                'detalle'         => 'Álbum tapa dura 30x30cm, 40 páginas impresas en papel mate.',
                'tamanio'         => '30x30cm',
                'unidad'          => 'unidad',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto'          => 'Fotografías digitales',
                'detalle'         => 'Pack de fotografías editadas entregadas en USB y galería online.',
                'tamanio'         => null,
                'unidad'          => 'pack',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto'          => 'Anuario escolar',
                'detalle'         => 'Anuario impreso full color, tapa dura personalizada con logo del colegio.',
                'tamanio'         => 'A4',
                'unidad'          => 'unidad',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto'          => 'Fotolibro tamaño mediano',
                'detalle'         => 'Fotolibro 20x20cm, 20 páginas, tapa blanda personalizada.',
                'tamanio'         => '20x20cm',
                'unidad'          => 'unidad',
                'estado'          => 'ACTIVO',
            ],
        ]);
    }
}
