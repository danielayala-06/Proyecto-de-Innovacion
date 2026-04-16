<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CotizacionesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('cotizaciones')->insert([
            'num_dias_vigencia'=>7,
            'fecha_registro'=>date('Y-m-d'),
            'fecha_hora_inicio'=>'2026-05-01 10:00:00',
            'fecha_hora_fin'=>'2026-05-01 18:00:00',
            'direccion'=>'Av. Principal 123',
            'latitud'=>-12.0464,
            'longitud'=>-77.0428,
            'estado'=>'pendiente',
            'total_estimado'=>1200,
            'id_cliente'=>1,
            'id_usuario'=>1
        ]);
    }
}
