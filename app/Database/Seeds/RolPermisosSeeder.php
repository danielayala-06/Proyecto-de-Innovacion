<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolPermisosSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Administrador: todos los permisos
            ['id_rol' => 1, 'id_permiso' => 1],
            ['id_rol' => 1, 'id_permiso' => 2],
            ['id_rol' => 1, 'id_permiso' => 3],
            ['id_rol' => 1, 'id_permiso' => 4],
            ['id_rol' => 1, 'id_permiso' => 5],
            ['id_rol' => 1, 'id_permiso' => 6],
            ['id_rol' => 1, 'id_permiso' => 7],
            ['id_rol' => 1, 'id_permiso' => 8],
            ['id_rol' => 1, 'id_permiso' => 9],
            ['id_rol' => 1, 'id_permiso' => 10],
            ['id_rol' => 1, 'id_permiso' => 11],
            ['id_rol' => 1, 'id_permiso' => 12],
            ['id_rol' => 1, 'id_permiso' => 13],
            ['id_rol' => 1, 'id_permiso' => 14],
            ['id_rol' => 1, 'id_permiso' => 15],
            ['id_rol' => 1, 'id_permiso' => 16],
            ['id_rol' => 1, 'id_permiso' => 17],
            ['id_rol' => 1, 'id_permiso' => 18],
            ['id_rol' => 1, 'id_permiso' => 19],
            ['id_rol' => 1, 'id_permiso' => 20],
            ['id_rol' => 1, 'id_permiso' => 21],
            ['id_rol' => 1, 'id_permiso' => 22],
            ['id_rol' => 1, 'id_permiso' => 23],
            ['id_rol' => 1, 'id_permiso' => 24],
            // Visualizador: solo VER de cada módulo (permisos 1,5,9,13,17,21)
            ['id_rol' => 2, 'id_permiso' => 1],
            ['id_rol' => 2, 'id_permiso' => 5],
            ['id_rol' => 2, 'id_permiso' => 9],
            ['id_rol' => 2, 'id_permiso' => 13],
            ['id_rol' => 2, 'id_permiso' => 17],
            ['id_rol' => 2, 'id_permiso' => 21],
        ];

        $this->db->table('rol_permisos')->insertBatch($data);
    }
}
