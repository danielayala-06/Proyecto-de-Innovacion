<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('usuarios')->insertBatch([
            [
                'id_persona'   => 1,
                'id_rol'       => 1, // Administrador
                'nombre_user'     => 'Admin',
                'password'     => password_hash('Admin123!', PASSWORD_BCRYPT),
                'tipo_usuario' => 'ADMINISTRADOR',
                'estado'       => 'ACTIVO',
            ],
            [
                'id_persona'   => 4,
                'id_rol'       => 2, // Visualizador
                'nombre_user'     => 'mariana.flores',
                'password'     => password_hash('Invitado123!', PASSWORD_BCRYPT),
                'tipo_usuario' => 'INVITADO',
                'estado'       => 'ACTIVO',
            ],
        ]);
    }
}
