<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run()
    {
        // Personas 1-4 usadas para usuarios del sistema
        // Roles: 1=Administrador, 2=Vendedor, 3=Fotógrafo, 4=Supervisor
        $data = [
            [
                'id_persona'    => 1,
                'id_rol'        => 1,
                'nombre_user'   => 'carlos.admin',
                'password_hash' => password_hash('Admin1234!', PASSWORD_DEFAULT),
                'estado'        => 'ACTIVO',
            ],
            [
                'id_persona'    => 2,
                'id_rol'        => 2,
                'nombre_user'   => 'maria.ventas',
                'password_hash' => password_hash('Ventas123!', PASSWORD_DEFAULT),
                'estado'        => 'ACTIVO',
            ],
            [
                'id_persona'    => 3,
                'id_rol'        => 3,
                'nombre_user'   => 'jorge.foto',
                'password_hash' => password_hash('Foto1234!', PASSWORD_DEFAULT),
                'estado'        => 'ACTIVO',
            ],
            [
                'id_persona'    => 4,
                'id_rol'        => 4,
                'nombre_user'   => 'ana.supervisor',
                'password_hash' => password_hash('Super123!', PASSWORD_DEFAULT),
                'estado'        => 'ACTIVO',
            ],
        ];

        $this->db->table('usuarios')->insertBatch($data);
    }
}
