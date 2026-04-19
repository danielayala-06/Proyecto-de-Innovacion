<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReprogramacionesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('reprogramaciones')->insertBatch([
             [
                 'id_contrato'              => 1,
                 'fecha_anterior'           => '2025-12-15 16:00:00',
                 'fecha_nueva'              => '2025-12-20 16:00:00',
                 'motivo'                   => 'Cliente solicitó cambio de fecha por viaje de familiares.',
                 'fecha_cambio'             => '2025-09-10 10:30:00',
                 'fecha_limite_aplicacion'  => '2025-10-10', // fecha_cambio + 30 días
             ],
             [
                 'id_contrato'              => 2,
                 'fecha_anterior'           => '2025-10-05 08:00:00',
                 'fecha_nueva'              => '2025-10-10 08:00:00',
                 'motivo'                   => 'El colegio reprogramó sus actividades internas.',
                 'fecha_cambio'             => '2025-09-01 09:00:00',
                 'fecha_limite_aplicacion'  => '2025-10-01',
             ],
         ]);
    }
}
