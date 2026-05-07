<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReglasPaquetesSeeder extends Seeder
{
    public function run()
    {
        // Paquetes: 1=Inicial Básico, 2=Primaria Completo, 3=Secundaria Premium, 4=Secundaria Estándar
        $data = [
            [
                'id_paquete'     => 2,
                'descripcion'    => 'Si el pedido supera 25 alumnos, se incluye un cuadro grupal gratis por sección.',
                'tipo_condicion' => 'CANTIDAD_MIN',
                'valor_condicion'=> 25.00,
                'tipo_beneficio' => 'producto_gratis',
                'valor_beneficio'=> 'Cuadro Grupal 50x70',
            ],
            [
                'id_paquete'     => 3,
                'descripcion'    => 'Si el pedido supera 30 alumnos, se incluye una sesión de exteriores sin costo.',
                'tipo_condicion' => 'CANTIDAD_MIN',
                'valor_condicion'=> 30.00,
                'tipo_beneficio' => 'sesion_unica',
                'valor_beneficio'=> 'Sesión fotográfica de exteriores incluida',
            ],
            [
                'id_paquete'     => 3,
                'descripcion'    => 'Máximo 40 alumnos por paquete premium. Grupos mayores requieren cotización especial.',
                'tipo_condicion' => 'CANTIDAD_MAX',
                'valor_condicion'=> 40.00,
                'tipo_beneficio' => 'otro',
                'valor_beneficio'=> 'Cotización especial para grupos > 40',
            ],
            [
                'id_paquete'     => 4,
                'descripcion'    => 'Para grupos de más de 20 alumnos en paquete estándar, se añade llavero adicional.',
                'tipo_condicion' => 'CANTIDAD_MIN',
                'valor_condicion'=> 20.00,
                'tipo_beneficio' => 'producto_gratis',
                'valor_beneficio'=> 'Llavero fotográfico adicional por alumno',
            ],
        ];

        $this->db->table('reglas_paquetes')->insertBatch($data);
    }
}
