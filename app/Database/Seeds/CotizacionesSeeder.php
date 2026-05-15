<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CotizacionesSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('cotizaciones')->truncate();

        // Estados posibles: PENDIENTE | APROBADA | RECHAZADA | EXPIRADA
        // EXPIRADA = aprobada pero no convertida a contrato dentro de los 30 días
        $data = [
            [
                'id_cliente'      => 1,
                'id_usuario'      => 2,
                'fecha_registro'  => '2026-03-10',
                'observaciones'   => 'Cliente interesado en paquete completo para 5to de secundaria.',
                'total_estimado'  => 13500.00,
                'estado'          => 'APROBADA',
            ],
            [
                'id_cliente'      => 2,
                'id_usuario'      => 2,
                'fecha_registro'  => '2026-03-15',
                'observaciones'   => 'Paquete primaria, pendiente confirmación de número de alumnos.',
                'total_estimado'  => 8400.00,
                'estado'          => 'PENDIENTE',
            ],
            [
                'id_cliente'      => 3,
                'id_usuario'      => 1,
                'fecha_registro'  => '2026-04-02',
                'observaciones'   => null,
                'total_estimado'  => 6000.00,
                'estado'          => 'APROBADA',
            ],
            [
                'id_cliente'      => 4,
                'id_usuario'      => 2,
                'fecha_registro'  => '2026-04-20',
                'observaciones'   => 'Cliente solicitó descuento por pago adelantado.',
                'total_estimado'  => 3200.00,
                'estado'          => 'RECHAZADA',
            ],
            [
                'id_cliente'      => 4,
                'id_usuario'      => 1,
                'fecha_registro'  => '2026-01-20',
                'observaciones'   => 'Cotización aprobada pero no convertida a contrato a tiempo.',
                'total_estimado'  => 2850.00,
                'estado'          => 'EXPIRADA',
            ],
        ];

        $this->db->table('cotizaciones')->insertBatch($data);
    }
}
