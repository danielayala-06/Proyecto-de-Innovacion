<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PersonasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombres'          => 'Carlos Andrés',
                'apellidos'        => 'Mendoza Ríos',
                'telefono'         => '987654321',
                'tel_alternativo'  => null,
                'correo'           => 'carlos.mendoza@gmail.com',
                'tipo_documento'   => 'DNI',
                'numero_documento' => '45123678',
            ],
            [
                'nombres'          => 'Lucía Patricia',
                'apellidos'        => 'Torres Vega',
                'telefono'         => '912345678',
                'tel_alternativo'  => '945678123',
                'correo'           => 'lucia.torres@hotmail.com',
                'tipo_documento'   => 'DNI',
                'numero_documento' => '73891234',
            ],
            [
                'nombres'          => 'Roberto',
                'apellidos'        => 'Sánchez Paredes',
                'telefono'         => '999888777',
                'tel_alternativo'  => null,
                'correo'           => 'rsanchez@empresa.pe',
                'tipo_documento'   => 'CE',
                'numero_documento' => 'CE001234',
            ],
            [
                'nombres'          => 'Mariana Isabel',
                'apellidos'        => 'Flores Huamán',
                'telefono'         => '956789012',
                'tel_alternativo'  => '934567890',
                'correo'           => 'mflores@outlook.com',
                'tipo_documento'   => 'DNI',
                'numero_documento' => '61234567',
            ],
            [
                'nombres'           => 'MORGAN MURILO',
                'apellidos'         => 'BONDIOLI MOLINA',
                'telefono'          => '978576845',
                'tel_alternativo'   =>  null,
                'correo'            => 'morgan123@gmail.com',
                'numero_documento'  => '87634526',
                'tipo_documento'    => 'DNI',
            ],
            [
                'nombres'           => 'DIGGY TONY',
                'apellidos'         => 'FELIX TIPPACTI',
                'telefono'          => '967356695',
                'tel_alternativo'   => '984357889',
                'correo'            => 'diggytonidotta2@hotmail.com',
                'numero_documento'  => '39879656',
                'tipo_documento'    => 'DNI'
            ],
            [
                'nombres'           => 'HENRY MANUEL',
                'apellidos'         => 'SAMANIEGO PACHAS',
                'telefono'          => '987678765',
                'tel_alternativo'   => null,
                'correo'            => 'samikeykopresidente@gmail.com',
                'numero_documento'  => '78654565',
                'tipo_documento'    => 'DNI'
            ],
            [
                'nombres'           => 'JOAO SOSA',
                'apellidos'         => 'CASTILLA HERRERA',
                'telefono'          => '987567346',
                'tel_alternativo'   => null,
                'correo'            => 'joao_sosa_01@gmail.com',
                'numero_documento'  => '68756789',
                'tipo_documento'    => 'DNI'
            ],
            [
                'nombres'           => 'JOSE FABRICIO',
                'apellidos'         => 'CASTILLA HERRERA',
                'telefono'          => '978654318',
                'tel_alternativo'   => null,
                'correo'            => 'wedo_castilla@gmail.com',
                'numero_documento'  => '75678978',
                'tipo_documento'    => 'DNI'
            ],
            [
                'nombres'           => 'ARELLA',
                'apellidos'         => 'TAPIA HERNANDEZ',
                'telefono'          => '987678298',
                'tel_alternativo'   => null,
                'correo'            => 'tuarellitadediggy@gmail.com',
                'numero_documento'  => '76982415',
                'tipo_documento'    => 'DNI'
            ],
        ];

        $this->db->table('personas')->insertBatch($data);
    }
}
