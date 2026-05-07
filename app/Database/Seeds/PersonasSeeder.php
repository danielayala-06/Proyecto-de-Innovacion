<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PersonasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombres'          => 'Carlos Alberto',
                'apellidos'        => 'Ramírez Torres',
                'telefono'         => '987654321',
                'correo'           => 'carlos.ramirez@gmail.com',
                'tel_alternativo'  => '056-234567',
                'numero_documento' => '45678901',
                'tipo_documento'   => 'DNI',
            ],
            [
                'nombres'          => 'María Elena',
                'apellidos'        => 'Flores Huanca',
                'telefono'         => '912345678',
                'correo'           => 'maria.flores@hotmail.com',
                'tel_alternativo'  => null,
                'numero_documento' => '72345890',
                'tipo_documento'   => 'DNI',
            ],
            [
                'nombres'          => 'Jorge Luis',
                'apellidos'        => 'Mendoza Quispe',
                'telefono'         => '934567890',
                'correo'           => 'jmendoza@empresa.pe',
                'tel_alternativo'  => '056-345678',
                'numero_documento' => '61234567',
                'tipo_documento'   => 'DNI',
            ],
            [
                'nombres'          => 'Ana Lucía',
                'apellidos'        => 'Vargas Huamán',
                'telefono'         => '945678901',
                'correo'           => 'ana.vargas@gmail.com',
                'tel_alternativo'  => null,
                'numero_documento' => '80123456',
                'tipo_documento'   => 'DNI',
            ],
            [
                'nombres'          => 'Roberto',
                'apellidos'        => 'Smith Johnson',
                'telefono'         => '956789012',
                'correo'           => 'rsmith@outlook.com',
                'tel_alternativo'  => '056-456789',
                'numero_documento' => 'CE-001234',
                'tipo_documento'   => 'CE',
            ],
            [
                'nombres'          => 'Lucía Patricia',
                'apellidos'        => 'Castro Benites',
                'telefono'         => '967890123',
                'correo'           => 'lcastro@gmail.com',
                'tel_alternativo'  => null,
                'numero_documento' => '70987654',
                'tipo_documento'   => 'DNI',
            ],
            [
                'nombres'          => 'Miguel Ángel',
                'apellidos'        => 'Prado Salinas',
                'telefono'         => '978901234',
                'correo'           => 'mprado@yahoo.com',
                'tel_alternativo'  => '056-567890',
                'numero_documento' => '48765432',
                'tipo_documento'   => 'DNI',
            ],
            [
                'nombres'          => 'Sandra Beatriz',
                'apellidos'        => 'León Chávez',
                'telefono'         => '989012345',
                'correo'           => 'sleon@gmail.com',
                'tel_alternativo'  => null,
                'numero_documento' => '55432198',
                'tipo_documento'   => 'DNI',
            ],
        ];

        $this->db->table('personas')->insertBatch($data);
    }
}
