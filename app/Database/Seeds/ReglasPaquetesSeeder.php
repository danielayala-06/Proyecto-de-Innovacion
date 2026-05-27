<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReglasPaquetesSeeder extends Seeder
{
    public function run()
    {
        $pId = fn(string $n) => (int)($this->db->table('paquetes')
            ->where('nombre_paquete', $n)->get()->getRowArray()['id_paquete'] ?? 0);

        $prId = fn(string $n) => (int)($this->db->table('productos')
            ->where('nombre_producto', $n)->get()->getRowArray()['id_producto'] ?? 0);

        // IDs de productos de bonificación
        $idCuadroLam  = $prId('Cuadro Laminado Docente 40x30');
        $idCuadroVid  = $prId('Cuadro de Vidrio Docente 45x30');
        $idAnuarioBigI = $prId('Anuario Big Inicial');
        $idAnuarioBigP = $prId('Anuario Big Primaria');

        // IDs de los 8 packs (4 Inicial + 4 Primaria)
        $packs = [
            'Pack Mi Primera Promo',
            'Pack Mis Recuerdos Inicial',
            'Pack Premium Inicial',
            'Pack Premium Gold Inicial',
            'Pack Adiós Primaria',
            'Pack Mis Recuerdos Primaria',
            'Pack Premium Primaria',
            'Pack Premium Gold Primaria',
        ];

        $reglas = [

            // ────────────────────────────────────────────────────────────────
            // 1. ELEGIBILIDAD_MIN 14 — aplica a los 8 packs
            // ────────────────────────────────────────────────────────────────
            [
                'regla' => [
                    'descripcion'           => 'Mínimo 14 alumnos para contratar este pack.',
                    'tipo_condicion'        => 'ELEGIBILIDAD_MIN',
                    'valor_condicion'       => 14.00,
                    'tipo_beneficio'        => 'otro',
                    'id_producto_beneficio' => null,
                    'valor_beneficio'       => 'Paquete no disponible para grupos menores de 14 alumnos.',
                ],
                'items' => array_map(fn($n) => ['tipo_item' => 'paquete', 'id_referencia' => $pId($n)], $packs),
            ],

            // ────────────────────────────────────────────────────────────────
            // 2. CANTIDAD_MIN 15 → Cuadro Laminado Docente 40x30 — todos los packs
            // ────────────────────────────────────────────────────────────────
            [
                'regla' => [
                    'descripcion'           => '+15 alumnos: cuadro laminado 40x30 para el docente incluido.',
                    'tipo_condicion'        => 'CANTIDAD_MIN',
                    'valor_condicion'       => 15.00,
                    'tipo_beneficio'        => 'producto_gratis',
                    'id_producto_beneficio' => $idCuadroLam,
                    'valor_beneficio'       => 'Cuadro Laminado Docente 40x30',
                ],
                'items' => array_map(fn($n) => ['tipo_item' => 'paquete', 'id_referencia' => $pId($n)], $packs),
            ],

            // ────────────────────────────────────────────────────────────────
            // 3. CANTIDAD_MIN 20 → Cuadro de Vidrio Docente 45x30
            //    Pack Mi Primera Promo, Pack Adiós Primaria,
            //    Pack Premium Gold Inicial, Pack Premium Gold Primaria
            // ────────────────────────────────────────────────────────────────
            [
                'regla' => [
                    'descripcion'           => '+20 alumnos: cuadro de vidrio 45x30 para el docente incluido.',
                    'tipo_condicion'        => 'CANTIDAD_MIN',
                    'valor_condicion'       => 20.00,
                    'tipo_beneficio'        => 'producto_gratis',
                    'id_producto_beneficio' => $idCuadroVid,
                    'valor_beneficio'       => 'Cuadro de Vidrio Docente 45x30',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => $pId('Pack Mi Primera Promo')],
                    ['tipo_item' => 'paquete', 'id_referencia' => $pId('Pack Adiós Primaria')],
                    ['tipo_item' => 'paquete', 'id_referencia' => $pId('Pack Premium Gold Inicial')],
                    ['tipo_item' => 'paquete', 'id_referencia' => $pId('Pack Premium Gold Primaria')],
                ],
            ],

            // ────────────────────────────────────────────────────────────────
            // 4. CANTIDAD_MIN 20 → Anuario Big Inicial para el docente
            //    Pack Mis Recuerdos Inicial, Pack Premium Inicial
            // ────────────────────────────────────────────────────────────────
            [
                'regla' => [
                    'descripcion'           => '+20 alumnos: anuario Big Inicial para el docente incluido.',
                    'tipo_condicion'        => 'CANTIDAD_MIN',
                    'valor_condicion'       => 20.00,
                    'tipo_beneficio'        => 'producto_gratis',
                    'id_producto_beneficio' => $idAnuarioBigI,
                    'valor_beneficio'       => 'Anuario Big Inicial',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => $pId('Pack Mis Recuerdos Inicial')],
                    ['tipo_item' => 'paquete', 'id_referencia' => $pId('Pack Premium Inicial')],
                ],
            ],

            // ────────────────────────────────────────────────────────────────
            // 5. CANTIDAD_MIN 20 → Anuario Big Primaria para el docente
            //    Pack Mis Recuerdos Primaria, Pack Premium Primaria
            // ────────────────────────────────────────────────────────────────
            [
                'regla' => [
                    'descripcion'           => '+20 alumnos: anuario Big Primaria para el docente incluido.',
                    'tipo_condicion'        => 'CANTIDAD_MIN',
                    'valor_condicion'       => 20.00,
                    'tipo_beneficio'        => 'producto_gratis',
                    'id_producto_beneficio' => $idAnuarioBigP,
                    'valor_beneficio'       => 'Anuario Big Primaria',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => $pId('Pack Mis Recuerdos Primaria')],
                    ['tipo_item' => 'paquete', 'id_referencia' => $pId('Pack Premium Primaria')],
                ],
            ],
        ];

        foreach ($reglas as $entry) {
            $items = array_filter($entry['items'], fn($i) => $i['id_referencia'] !== 0);
            if (empty($items)) {
                log_message('warning', "[ReglasPaquetesSeeder] Saltando regla '{$entry['regla']['descripcion']}': sin ítems resueltos.");
                continue;
            }

            $this->db->table('reglas_paquetes')->insert($entry['regla']);
            $idRegla = $this->db->insertID();

            foreach ($items as $item) {
                $this->db->table('reglas_items')->insert([
                    'id_regla'      => $idRegla,
                    'tipo_item'     => $item['tipo_item'],
                    'id_referencia' => $item['id_referencia'],
                ]);
            }
        }
    }
}
