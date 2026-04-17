<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('usuarios')->insert([
            'estado'=>'activo',
            'nom_user'=>'admin',
            'password'=>password_hash('123456', PASSWORD_DEFAULT),
            'tipo_usuario'=>'interno',
            'id_persona'=>1,
            'id_rol'=>1
        ]);
    }
}
