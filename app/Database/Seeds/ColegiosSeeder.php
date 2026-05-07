<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ColegiosSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre_colegio' => 'I.E. San Luis Gonzaga',
                'distrito'       => 'Ica',
                'provincia'      => 'Ica',
                'estado'         => 'ACTIVO',
            ],
            [
                'nombre_colegio' => 'I.E. María Inmaculada',
                'distrito'       => 'Ica',
                'provincia'      => 'Ica',
                'estado'         => 'ACTIVO',
            ],
            [
                'nombre_colegio' => 'I.E. Nuestra Señora del Carmen',
                'distrito'       => 'Chincha Alta',
                'provincia'      => 'Chincha',
                'estado'         => 'ACTIVO',
            ],
            [
                'nombre_colegio' => 'I.E. José de la Torre Ugarte',
                'distrito'       => 'Pisco',
                'provincia'      => 'Pisco',
                'estado'         => 'ACTIVO',
            ],
            [
                'nombre_colegio' => 'I.E. Abraham Valdelomar',
                'distrito'       => 'Ica',
                'provincia'      => 'Ica',
                'estado'         => 'INACTIVO',
            ],
        ];

        $this->db->table('colegios')->insertBatch($data);
    }
}
