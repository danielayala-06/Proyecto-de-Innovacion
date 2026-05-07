<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesPermisosSeeder extends Seeder
{
    public function run()
    {
        // El Administrador (rol 1) tiene todos los permisos (1-24)
        $data = [];
        for ($i = 1; $i <= 24; $i++) {
            $data[] = ['id_rol' => 1, 'id_permiso' => $i];
        }

        // Vendedor (rol 2): solo VER y CREAR en Clientes(5,6), Cotizaciones(9,10), Contratos(13,14)
        $vendedorPermisos = [5, 6, 9, 10, 13, 14];
        foreach ($vendedorPermisos as $idPermiso) {
            $data[] = ['id_rol' => 2, 'id_permiso' => $idPermiso];
        }

        // Fotógrafo (rol 3): solo VER en todos los módulos (permisos impares 1,5,9,13,17,21)
        $fotografoPermisos = [1, 5, 9, 13, 17, 21];
        foreach ($fotografoPermisos as $idPermiso) {
            $data[] = ['id_rol' => 3, 'id_permiso' => $idPermiso];
        }

        // Supervisor (rol 4): VER y EDITAR en todos (1,3,5,7,9,11,13,15,17,19,21,23)
        $supervisorPermisos = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23];
        foreach ($supervisorPermisos as $idPermiso) {
            $data[] = ['id_rol' => 4, 'id_permiso' => $idPermiso];
        }

        $this->db->table('rol_permisos')->insertBatch($data);
    }
}
