<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ColegiosSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre_colegio' => 'I.E. PROLOG',
                'distrito'       => 'CHINCHA ALTA', 
                'provincia'      => 'CHINCHA',
                'estado'         => 'ACTIVO',
            ],
            [
                'nombre_colegio' => 'I.E. AURELIO MOISÈS FLORES',
                'distrito'       => 'CHINCHA ALTA', 
                'provincia'      => 'CHINCHA',
                'estado'         => 'ACTIVO',
            ],
            [
                'nombre_colegio' => 'I.E. DONALD SCARROW',
                'distrito'       => 'CHINCHA ALTA',
                'provincia'      => 'CHINCHA',
                'estado'         => 'ACTIVO',
            ],
            [
                'nombre_colegio' => 'I.E. ALEXANDER VON HUMBOLT',
                'distrito'       => 'PISCO',
                'provincia'      => 'PISCO',
                'estado'         => 'ACTIVO',
            ],
            [
                'nombre_colegio' => 'I.E. ABRAHAM VALDEROMAR',
                'distrito'       => 'ICA',
                'provincia'      => 'ICA',
                'estado'         => 'INACTIVO',
            ],
        ];

        $this->db->table('colegios')->insertBatch($data);
    }
}
