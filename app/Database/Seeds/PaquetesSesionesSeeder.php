<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesSesionesSeeder extends Seeder
{
    public function run()
    {
        // Paquetes seeded: 1=Inicial Básico, 2=Primaria Completo,
        //                  3=Secundaria Premium, 4=Secundaria Estándar
        $data = [
            // Paquete 1 – Inicial Básico: 1 sesión en el colegio
            [
                'id_paquete'        => 1,
                'tipo_sesion'       => 'colegio',
                'lugar_descripcion' => 'Salón de clases o patio principal',
                'num_sesiones'      => 1,
            ],

            // Paquete 2 – Primaria Completo: 1 colegio + 1 exteriores
            [
                'id_paquete'        => 2,
                'tipo_sesion'       => 'colegio',
                'lugar_descripcion' => 'Instalaciones del colegio',
                'num_sesiones'      => 1,
            ],
            [
                'id_paquete'        => 2,
                'tipo_sesion'       => 'exteriores',
                'lugar_descripcion' => 'Parque o ambiente al aire libre',
                'num_sesiones'      => 1,
            ],

            // Paquete 3 – Secundaria Premium: 1 colegio + 2 exteriores + 1 estudio
            [
                'id_paquete'        => 3,
                'tipo_sesion'       => 'colegio',
                'lugar_descripcion' => 'Instalaciones del colegio',
                'num_sesiones'      => 1,
            ],
            [
                'id_paquete'        => 3,
                'tipo_sesion'       => 'exteriores',
                'lugar_descripcion' => null,
                'num_sesiones'      => 2,
            ],
            [
                'id_paquete'        => 3,
                'tipo_sesion'       => 'estudio',
                'lugar_descripcion' => 'Estudio fotográfico principal',
                'num_sesiones'      => 1,
            ],

            // Paquete 4 – Secundaria Estándar: 1 colegio + 1 exteriores
            [
                'id_paquete'        => 4,
                'tipo_sesion'       => 'colegio',
                'lugar_descripcion' => 'Instalaciones del colegio',
                'num_sesiones'      => 1,
            ],
            [
                'id_paquete'        => 4,
                'tipo_sesion'       => 'exteriores',
                'lugar_descripcion' => null,
                'num_sesiones'      => 1,
            ],
        ];

        $this->db->table('paquetes_sesiones')->insertBatch($data);
    }
}
