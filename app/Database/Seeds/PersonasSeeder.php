<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PersonasSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('personas')->insertBatch([
            [
                'nombres'=>'MORGAN MURILO',
                'apellidos'=>'BONDIOLI MOLINA',
                'telefono'=>'978576845',
                'correo'=>'morgan123@gmail.com',
                'numero_documento'=>'87634526',
                'tipo_documento'=>'DNI'
            ],
            [
                'nombres'=>'DIGGY TONY',
                'apellidos'=>'FELIX TIPPACTI',
                'telefono'=>'967356695',
                'correo'=>'diggytonidotta2@hotmail.com',
                'numero_documento'=>'39879656',
                'tipo_documento'=>'DNI'
            ],
            [
                'nombres'=>'HENRY MANUEL',
                'apellidos'=>'SAMANIEGO PACHAS',
                'telefono'=>'987678765',
                'correo'=>'samikeykopresidente@gmail.com',
                'numero_documento'=>'78654565',
                'tipo_documento'=>'DNI'
            ],
            [
                'nombres'=>'JOAO SOSA',
                'apellidos'=>'CASTILLA HERRERA',
                'telefono'=>'987567346',
                'correo'=>'joao_sosa_01@gmail.com',
                'numero_documento'=>'68756789',
                'tipo_documento'=>'DNI'
            ],
            [
                'nombres'=>'JOSE FABRICIO',
                'apellidos'=>'CASTILLA HERRERA',
                'telefono'=>'978654318',
                'correo'=>'wedo_castilla@gmail.com',
                'numero_documento'=>'75678978',
                'tipo_documento'=>'DNI'
            ],
            [
                'nombres'=>'ARELLA',
                'apellidos'=>'TAPIA HERNANDEZ',
                'telefono'=>'987678298',
                'correo'=>'tuarellitadediggy@gmail.com',
                'numero_documento'=>'76982415',
                'tipo_documento'=>'DNI'
            ],
        ]);
    }
}
