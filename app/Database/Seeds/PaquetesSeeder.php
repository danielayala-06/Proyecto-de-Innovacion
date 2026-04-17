<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('paquetes')->insertBatch([
            [
                'nombre_paquete'=>'Paquete Básico',
                'precio_base'=>500,
                'detalle_paquete'=>'Incluye fotografía',
                'estado'=>'activo'
            ],
            [
                'nombre_paquete'=>'Paquete Premium',
                'precio_base'=>1200,
                'detalle_paquete'=>'Foto + video + álbum',
                'estado'=>'activo'
            ]
        ]);
    }
}
