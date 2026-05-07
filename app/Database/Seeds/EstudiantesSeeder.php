<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EstudiantesSeeder extends Seeder
{
    public function run()
    {
        // Apoderados: 1-4, Promociones: 1-4
        $data = [
            [
                'id_apoderado'     => 1,
                'id_promocion'     => 1,
                'nombres'          => 'Diego Alejandro',
                'apellidos'        => 'Smith Flores',
                'fecha_nacimiento' => '2008-06-15',
                'color_fav'        => 'Azul',
                'profesion_futura' => 'Ingeniero de sistemas',
            ],
            [
                'id_apoderado'     => 1,
                'id_promocion'     => 1,
                'nombres'          => 'Valeria',
                'apellidos'        => 'Smith Flores',
                'fecha_nacimiento' => '2010-02-20',
                'color_fav'        => 'Morado',
                'profesion_futura' => 'Médica',
            ],
            [
                'id_apoderado'     => 2,
                'id_promocion'     => 2,
                'nombres'          => 'Sebastián',
                'apellidos'        => 'Castro Pérez',
                'fecha_nacimiento' => '2008-11-03',
                'color_fav'        => 'Verde',
                'profesion_futura' => 'Futbolista',
            ],
            [
                'id_apoderado'     => 3,
                'id_promocion'     => 3,
                'nombres'          => 'Camila Sofía',
                'apellidos'        => 'Prado Vargas',
                'fecha_nacimiento' => '2014-08-12',
                'color_fav'        => 'Rosa',
                'profesion_futura' => 'Veterinaria',
            ],
            [
                'id_apoderado'     => 4,
                'id_promocion'     => 3,
                'nombres'          => 'Mateo',
                'apellidos'        => 'León García',
                'fecha_nacimiento' => '2015-01-25',
                'color_fav'        => 'Rojo',
                'profesion_futura' => 'Astronauta',
            ],
        ];

        $this->db->table('estudiantes')->insertBatch($data);
    }
}
