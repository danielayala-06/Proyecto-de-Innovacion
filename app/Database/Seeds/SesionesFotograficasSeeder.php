<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SesionesFotograficasSeeder extends Seeder
{
    public function run()
    {
        // Promociones: 1-4
        $data = [
            [
                'id_promocion'     => 1,
                'fecha_hora_sesion'=> '2026-07-10 08:00:00',
                'tipo'             => 'colegio',
                'observaciones'    => 'Sesión individual en el colegio. Llevar uniforme de gala.',
                'estado'           => 'pendiente',
            ],
            [
                'id_promocion'     => 1,
                'fecha_hora_sesion'=> '2026-07-12 09:00:00',
                'tipo'             => 'exteriores',
                'observaciones'    => 'Sesión grupal en el parque. Coordinado con director.',
                'estado'           => 'pendiente',
            ],
            [
                'id_promocion'     => 2,
                'fecha_hora_sesion'=> '2026-06-20 07:30:00',
                'tipo'             => 'colegio',
                'observaciones'    => 'Sesión individual 5to B completada sin inconvenientes.',
                'estado'           => 'finalizado',
            ],
            [
                'id_promocion'     => 3,
                'fecha_hora_sesion'=> '2026-08-05 08:00:00',
                'tipo'             => 'exteriores',
                'observaciones'    => 'Sesión en playa de Ica. Pendiente permiso municipal.',
                'estado'           => 'pendiente',
            ],
            [
                'id_promocion'     => 4,
                'fecha_hora_sesion'=> '2026-05-15 09:00:00',
                'tipo'             => 'otro',
                'observaciones'    => 'Sesión cancelada por lluvia. Reprogramar.',
                'estado'           => 'cancelado',
            ],
        ];

        $this->db->table('sesiones_fotograficas')->insertBatch($data);
    }
}
