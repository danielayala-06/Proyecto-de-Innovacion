<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReglasPaquetesSeeder extends Seeder
{
    public function run()
    {
        // IDs de paquetes (ver PaquetesSeeder):
        //   11 = Pack Adios Primaria   12 = Pack Mis Recuerdos
        //   13 = Pack Premium          14 = Pack Premium Gold
        //
        // IDs de productos (ver ProductosSeeder):
        //    6 = Llavero Fotográfico
        //
        // Cada entrada define UNA regla y la lista de ítems (paquetes/productos)
        // a los que aplica. La relación es many-to-many vía tabla `reglas_items`.

        // IDs de productos para beneficios:
        //   1 = Cuadro Individual (30x40 cm)
        //   2 = Cuadro Grupal     (50x70 cm)
        //   3 = Anuario Escolar Tapa Dura (A4)

        $reglas = [
            // ── +15 alumnos: cuadro individual para el docente ────────────────
            // Una sola regla aplica a los 4 packs
            [
                'regla' => [
                    'descripcion'          => '+15 alumnos: cuadro laminado para el docente incluido.',
                    'tipo_condicion'       => 'CANTIDAD_MIN',
                    'valor_condicion'      => 15.00,
                    'tipo_beneficio'       => 'producto_gratis',
                    'id_producto_beneficio' => 1,
                    'valor_beneficio'      => 'Cuadro Individual',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => 11],
                    ['tipo_item' => 'paquete', 'id_referencia' => 12],
                    ['tipo_item' => 'paquete', 'id_referencia' => 13],
                    ['tipo_item' => 'paquete', 'id_referencia' => 14],
                ],
            ],

            // ── +20 alumnos: cuadro grupal para el docente ────────────────────
            // Aplica a Pack Adios Primaria y Pack Premium Gold
            [
                'regla' => [
                    'descripcion'          => '+20 alumnos: cuadro de vidrio grupal para el docente incluido.',
                    'tipo_condicion'       => 'CANTIDAD_MIN',
                    'valor_condicion'      => 20.00,
                    'tipo_beneficio'       => 'producto_gratis',
                    'id_producto_beneficio' => 2,
                    'valor_beneficio'      => 'Cuadro Grupal',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => 11],
                    ['tipo_item' => 'paquete', 'id_referencia' => 14],
                ],
            ],

            // ── +20 alumnos: anuario para el docente ──────────────────────────
            // Aplica a Pack Mis Recuerdos y Pack Premium
            [
                'regla' => [
                    'descripcion'          => '+20 alumnos: anuario para el docente incluido.',
                    'tipo_condicion'       => 'CANTIDAD_MIN',
                    'valor_condicion'      => 20.00,
                    'tipo_beneficio'       => 'producto_gratis',
                    'id_producto_beneficio' => 3,
                    'valor_beneficio'      => 'Anuario Escolar Tapa Dura',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => 12],
                    ['tipo_item' => 'paquete', 'id_referencia' => 13],
                ],
            ],

            // ── Límite 60 alumnos por sesión (Pack Premium) ───────────────────
            [
                'regla' => [
                    'descripcion'          => 'Máximo 60 alumnos por sesión. Grupos mayores requieren cotización especial.',
                    'tipo_condicion'       => 'CANTIDAD_MAX',
                    'valor_condicion'      => 60.00,
                    'tipo_beneficio'       => 'otro',
                    'id_producto_beneficio' => null,
                    'valor_beneficio'      => 'Cotización especial para grupos > 60',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => 13],
                ],
            ],

            // ── Llavero Fotográfico: envío gratis desde 30 unidades ──────────
            [
                'regla' => [
                    'descripcion'          => '+30 llaveros: envío gratuito al colegio incluido.',
                    'tipo_condicion'       => 'CANTIDAD_MIN',
                    'valor_condicion'      => 30.00,
                    'tipo_beneficio'       => 'otro',
                    'id_producto_beneficio' => null,
                    'valor_beneficio'      => 'Envío gratuito al colegio',
                ],
                'items' => [
                    ['tipo_item' => 'producto', 'id_referencia' => 6],
                ],
            ],
        ];

        foreach ($reglas as $entry) {
            $row = $entry['regla'];
            if (!array_key_exists('id_producto_beneficio', $row)) {
                $row['id_producto_beneficio'] = null;
            }
            $this->db->table('reglas_paquetes')->insert($row);
            $idRegla = $this->db->insertID();

            foreach ($entry['items'] as $item) {
                $this->db->table('reglas_items')->insert([
                    'id_regla'      => $idRegla,
                    'tipo_item'     => $item['tipo_item'],
                    'id_referencia' => $item['id_referencia'],
                ]);
            }
        }
    }
}
