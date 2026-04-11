<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CotizacionesPruebas extends Seeder
{
    public function run()
    {
        $cotizaciones = [
            [
                'dni'=>'78967389',
                'cliente'=>'DIGGY TONY F.',
                'paquete'=>'QUINO',
                'fecha_evento'=> '10-09-2026',
                'monto_acordado'=> '4500.00',
                'estado'=> 'VIGENTE',
                'created_at'=> date('d-m-y H:i'),
                'updated_at'=> null,
            ],
            [
                'dni'=>'45678765',
                'cliente'=>'MONTALVAN MIGUEL, PACHAS.',
                'paquete'=>'BODA',
                'fecha_evento'=> '12-20-2026',
                'monto_acordado'=> '5200.00',
                'estado'=> 'VIGENTE',
                'created_at'=> date('d-m-y H:i'),
                'updated_at'=> null,
            ],
            [
                'dni'=>'72878679',
                'cliente'=>'DAYANA EVELYN, T.',
                'paquete'=>'QUINO',
                'fecha_evento'=> '08-13-2026',
                'monto_acordado'=> '3500.00',
                'estado'=> 'VENCIDO',
                'created_at'=> '02/03/2026 - 15:32',
                'updated_at'=> null,
            ],
        ];

        $this->db->table('cotizacion_pruebas')->insertBatch($cotizaciones);
    }
}
