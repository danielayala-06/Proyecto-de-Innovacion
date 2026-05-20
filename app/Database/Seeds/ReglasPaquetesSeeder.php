<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReglasPaquetesSeeder extends Seeder
{
    public function run()
    {
        // Resolve IDs by name so the seeder is safe on any fresh DB
        $paqueteId  = fn(string $n) => (int) $this->db->table('paquetes')
            ->where('nombre_paquete', $n)->get()->getRowArray()['id_paquete'] ?? 0;

        $productoId = fn(string $n) => (int) $this->db->table('productos')
            ->where('nombre_producto', $n)->get()->getRowArray()['id_producto'] ?? 0;

        $idAP  = $paqueteId('Pack Adios Primaria');
        $idMR  = $paqueteId('Pack Mis Recuerdos');
        $idPP  = $paqueteId('Pack Premium');
        $idPG  = $paqueteId('Pack Premium Gold');

        $idCuadroInd  = $productoId('Cuadro Individual 30x40');
        $idCuadroGrp  = $productoId('Cuadro Grupal 50x70');
        $idAnuario    = $productoId('Anuario Escolar Premium');
        $idLlavero    = $productoId('Llavero Fotográfico');

        $reglas = [
            // +15 alumnos → cuadro individual para el docente (aplica a los 4 packs)
            [
                'regla' => [
                    'descripcion'           => '+15 alumnos: cuadro laminado para el docente incluido.',
                    'tipo_condicion'        => 'CANTIDAD_MIN',
                    'valor_condicion'       => 15.00,
                    'tipo_beneficio'        => 'producto_gratis',
                    'id_producto_beneficio' => $idCuadroInd,
                    'valor_beneficio'       => 'Cuadro Individual 30x40',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => $idAP],
                    ['tipo_item' => 'paquete', 'id_referencia' => $idMR],
                    ['tipo_item' => 'paquete', 'id_referencia' => $idPP],
                    ['tipo_item' => 'paquete', 'id_referencia' => $idPG],
                ],
            ],

            // +20 alumnos → cuadro grupal para el docente (Pack Adios Primaria y Pack Premium Gold)
            [
                'regla' => [
                    'descripcion'           => '+20 alumnos: cuadro de vidrio grupal para el docente incluido.',
                    'tipo_condicion'        => 'CANTIDAD_MIN',
                    'valor_condicion'       => 20.00,
                    'tipo_beneficio'        => 'producto_gratis',
                    'id_producto_beneficio' => $idCuadroGrp,
                    'valor_beneficio'       => 'Cuadro Grupal 50x70',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => $idAP],
                    ['tipo_item' => 'paquete', 'id_referencia' => $idPG],
                ],
            ],

            // +20 alumnos → anuario para el docente (Pack Mis Recuerdos y Pack Premium)
            [
                'regla' => [
                    'descripcion'           => '+20 alumnos: anuario para el docente incluido.',
                    'tipo_condicion'        => 'CANTIDAD_MIN',
                    'valor_condicion'       => 20.00,
                    'tipo_beneficio'        => 'producto_gratis',
                    'id_producto_beneficio' => $idAnuario,
                    'valor_beneficio'       => 'Anuario Escolar Premium',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => $idMR],
                    ['tipo_item' => 'paquete', 'id_referencia' => $idPP],
                ],
            ],

            // Límite 60 alumnos por sesión (Pack Premium)
            [
                'regla' => [
                    'descripcion'           => 'Máximo 60 alumnos por sesión. Grupos mayores requieren cotización especial.',
                    'tipo_condicion'        => 'CANTIDAD_MAX',
                    'valor_condicion'       => 60.00,
                    'tipo_beneficio'        => 'otro',
                    'id_producto_beneficio' => null,
                    'valor_beneficio'       => 'Cotización especial para grupos > 60',
                ],
                'items' => [
                    ['tipo_item' => 'paquete', 'id_referencia' => $idPP],
                ],
            ],

            // +30 llaveros → envío gratuito al colegio
            [
                'regla' => [
                    'descripcion'           => '+30 llaveros: envío gratuito al colegio incluido.',
                    'tipo_condicion'        => 'CANTIDAD_MIN',
                    'valor_condicion'       => 30.00,
                    'tipo_beneficio'        => 'otro',
                    'id_producto_beneficio' => null,
                    'valor_beneficio'       => 'Envío gratuito al colegio',
                ],
                'items' => [
                    ['tipo_item' => 'producto', 'id_referencia' => $idLlavero],
                ],
            ],
        ];

        foreach ($reglas as $entry) {
            // Skip rule if any referenced ID could not be resolved
            $allItems = $entry['items'];
            if (in_array(0, array_column($allItems, 'id_referencia'), true)) {
                log_message('warning', '[ReglasPaquetesSeeder] Skipping rule "' . $entry['regla']['descripcion'] . '" — one or more referenced IDs not found.');
                continue;
            }

            $this->db->table('reglas_paquetes')->insert($entry['regla']);
            $idRegla = $this->db->insertID();

            foreach ($allItems as $item) {
                $this->db->table('reglas_items')->insert([
                    'id_regla'      => $idRegla,
                    'tipo_item'     => $item['tipo_item'],
                    'id_referencia' => $item['id_referencia'],
                ]);
            }
        }
    }
}
