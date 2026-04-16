<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContratosSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('contratos')->insert([
            'fecha_contrato'=>date('Y-m-d'),
            'fecha_hora_inicio'=>'2026-05-01 10:00:00',
            'fecha_hora_fin'=>'2026-05-01 18:00:00',
            'id_cotizacion'=>1,
            'estado'=>'activo',
            'total_final'=>1200
        ]);
    }
}
