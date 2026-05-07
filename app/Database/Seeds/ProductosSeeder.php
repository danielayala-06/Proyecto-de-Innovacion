<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre_producto' => 'Cuadro Individual 30x40',
                'categoria'       => 'cuadro',
                'detalle'         => 'Impresión en papel fotográfico mate, con marco de madera',
                'tamanio'         => '30x40 cm',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Grupal 50x70',
                'categoria'       => 'cuadro',
                'detalle'         => 'Foto grupal de la promoción con marco plateado',
                'tamanio'         => '50x70 cm',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Anuario Escolar Premium',
                'categoria'       => 'anuario',
                'detalle'         => 'Anuario tapa dura con 80 páginas a full color',
                'tamanio'         => 'A4',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Anuario Escolar Básico',
                'categoria'       => 'anuario',
                'detalle'         => 'Anuario tapa blanda con 50 páginas',
                'tamanio'         => 'A4',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Photobook Clásico',
                'categoria'       => 'photobook',
                'detalle'         => 'Álbum fotográfico 20 páginas, tapa dura personalizada',
                'tamanio'         => '20x20 cm',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Llavero Fotográfico',
                'categoria'       => 'otro',
                'detalle'         => 'Llavero acrílico con foto individual',
                'tamanio'         => '5x5 cm',
                'estado'          => 'ACTIVO',
            ],
        ];

        $this->db->table('productos')->insertBatch($data);
    }
}
