<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // ── CUADROS PRIMARIA ──────────────────────────────────────────────
            [
                'nombre_producto' => 'Cuadro Maravillas del Mundo',
                'categoria'       => 'cuadro',
                'tamanio'         => '33x45 cm',
                'detalle'         => 'Acabado Brillante y Vidrio.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Académico',
                'categoria'       => 'cuadro',
                'tamanio'         => '58.5x45.5 cm',
                'detalle'         => 'Acabado Brillante y Vidrio.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Blanco Premium Brillante',
                'categoria'       => 'cuadro',
                'tamanio'         => '52x67 cm',
                'detalle'         => 'Acabado Brillante y Vidrio.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Brillante',
                'categoria'       => 'cuadro',
                'tamanio'         => '50.5x66 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Colores: Azul, Dorado, Blanco, Negro, Verde, Fucsia, Guinda.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Encajoado',
                'categoria'       => 'cuadro',
                'tamanio'         => '50.5x66 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Colores: White, Black, Brown, Blue.',
                'estado'          => 'ACTIVO',
            ],

            // ── CUADROS INICIAL — temáticos pequeños (33x45 cm) ───────────────
            [
                'nombre_producto' => 'Cuadro Soy una Princesa',
                'categoria'       => 'cuadro',
                'tamanio'         => '33x45 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño temático princesa.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Soy un Super Héroe',
                'categoria'       => 'cuadro',
                'tamanio'         => '33x45 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño temático super héroe.',
                'estado'          => 'ACTIVO',
            ],

            // ── CUADROS INICIAL — temáticos medianos (58.5x45.5 cm) ───────────
            [
                'nombre_producto' => 'Cuadro Mi Princesa',
                'categoria'       => 'cuadro',
                'tamanio'         => '58.5x45.5 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño Mi Princesa para niñas.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Soy el Mejor',
                'categoria'       => 'cuadro',
                'tamanio'         => '58.5x45.5 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño motivacional para niños.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Princesas Mágicas',
                'categoria'       => 'cuadro',
                'tamanio'         => '58.5x45.5 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño princesas mágicas.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Super Héroe',
                'categoria'       => 'cuadro',
                'tamanio'         => '58.5x45.5 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño super héroe para niños.',
                'estado'          => 'ACTIVO',
            ],

            // ── CUADROS INICIAL — temáticos premium (50.5x51 cm) ─────────────
            [
                'nombre_producto' => 'Cuadro Mi Príncipe Super Capy',
                'categoria'       => 'cuadro',
                'tamanio'         => '50.5x51 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño premium Mi Príncipe Super Capy.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Mi Princesa Capy Princess',
                'categoria'       => 'cuadro',
                'tamanio'         => '50.5x51 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño premium Mi Princesa Capy Princess.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Barbie Capibara',
                'categoria'       => 'cuadro',
                'tamanio'         => '50.5x51 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño premium Barbie Capibara.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro Avenger',
                'categoria'       => 'cuadro',
                'tamanio'         => '50.5x51 cm',
                'detalle'         => 'Acabado Brillante y Vidrio. Diseño premium Avenger.',
                'estado'          => 'ACTIVO',
            ],

            // ── CUADROS DOCENTE (productos de bonificación) ───────────────────
            [
                'nombre_producto' => 'Cuadro Laminado Docente 40x30',
                'categoria'       => 'cuadro',
                'tamanio'         => '40x30 cm',
                'detalle'         => 'Cuadro laminado para docente. Beneficio por grupo de 15 o más alumnos.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Cuadro de Vidrio Docente 45x30',
                'categoria'       => 'cuadro',
                'tamanio'         => '45x30 cm',
                'detalle'         => 'Cuadro de vidrio para docente. Beneficio por grupo de 20 o más alumnos.',
                'estado'          => 'ACTIVO',
            ],

            // ── ANUARIOS PRIMARIA ─────────────────────────────────────────────
            [
                'nombre_producto' => 'Anuario Small Primaria',
                'categoria'       => 'anuario',
                'tamanio'         => '20x20 cm',
                'detalle'         => 'Portada foto laminada y biocuero. 5 hojas, 10 páginas.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Anuario Medium Primaria',
                'categoria'       => 'anuario',
                'tamanio'         => '20x25 cm',
                'detalle'         => 'Portada foto laminada y biocuero. 5 hojas, 10 páginas.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Anuario Big Primaria',
                'categoria'       => 'anuario',
                'tamanio'         => '25x25 cm',
                'detalle'         => 'Portada foto laminada y biocuero. 5 hojas, 10 páginas.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Anuario Big Premium Primaria',
                'categoria'       => 'anuario',
                'tamanio'         => '25x30 cm',
                'detalle'         => 'Portada foto laminada y biocuero. 6 hojas, 12 páginas. Incluye hoja de guarda y versión digital.',
                'estado'          => 'ACTIVO',
            ],

            // ── ANUARIOS INICIAL ──────────────────────────────────────────────
            [
                'nombre_producto' => 'Anuario Medium Inicial',
                'categoria'       => 'anuario',
                'tamanio'         => '20x25 cm',
                'detalle'         => 'Portada foto laminada y biocuero. 5 hojas, 10 páginas.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Anuario Big Inicial',
                'categoria'       => 'anuario',
                'tamanio'         => '25x25 cm',
                'detalle'         => 'Portada foto laminada y biocuero. 5 hojas, 10 páginas.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Anuario Big Premium Inicial',
                'categoria'       => 'anuario',
                'tamanio'         => '25x25 cm',
                'detalle'         => 'Portada foto laminada y biocuero. 6 hojas, 12 páginas. Incluye hoja de guarda y versión digital.',
                'estado'          => 'ACTIVO',
            ],

            // ── ACCESORIOS ────────────────────────────────────────────────────
            [
                'nombre_producto' => 'Anuario Llavero',
                'categoria'       => 'otro',
                'tamanio'         => '5x5 cm',
                'detalle'         => 'Versión llavero del anuario escolar.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'USB Fotográfico',
                'categoria'       => 'otro',
                'tamanio'         => null,
                'detalle'         => 'USB con todas las fotos y video del paquete.',
                'estado'          => 'ACTIVO',
            ],
            [
                'nombre_producto' => 'Book Anillado de Firma',
                'categoria'       => 'otro',
                'tamanio'         => null,
                'detalle'         => 'Book anillado personalizado para firmas de compañeros.',
                'estado'          => 'ACTIVO',
            ],
        ];

        $this->db->table('productos')->insertBatch($data);
    }
}
