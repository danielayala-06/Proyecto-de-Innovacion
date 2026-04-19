<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermisosSeeder extends Seeder
{
    public function run()
    {
        // Guardamos un array con todos los modulos y acciones
        $modulos = ['COTIZACIONES', 'CONTRATOS', 'PAGOS', 'CLIENTES', 'PAQUETES', 'REPORTES'];
        $acciones = ['VER', 'CREAR', 'EDITAR', 'ELIMINAR'];

        $data = [];
        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                $data[] = [
                    'nombre_modulo' => $modulo,
                    'accion'        => $accion,
                ];
            }
        }

        $this->db->table('permisos')->insertBatch($data);
    }
}
