<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EmpresasSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('empresas')->insert([
            'razon_social'=>'Eventos SAC',
            'ruc'=>'20123456789',
            'nombre_comercial'=>'EventosPro',
            'telefono'=>'987654321',
            'correo'=>'empresa@test.com'
        ]);
    }
}
