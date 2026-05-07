<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClientesSeeder extends Seeder
{
    public function run()
    {
        // Personas 5-8 usadas para clientes
        $data = [
            [
                'id_persona'          => 5,
                'red_social'          => '@rsmith_foto',
                'metodo_comunicacion' => 'whatsapp',
                'acepta_promociones'  => true,
                'estado'              => 'ACTIVO',
            ],
            [
                'id_persona'          => 6,
                'red_social'          => 'lucia.castro.foto',
                'metodo_comunicacion' => 'correo',
                'acepta_promociones'  => true,
                'estado'              => 'ACTIVO',
            ],
            [
                'id_persona'          => 7,
                'red_social'          => null,
                'metodo_comunicacion' => 'llamada',
                'acepta_promociones'  => false,
                'estado'              => 'ACTIVO',
            ],
            [
                'id_persona'          => 8,
                'red_social'          => '@sleon_ica',
                'metodo_comunicacion' => 'whatsapp',
                'acepta_promociones'  => true,
                'estado'              => 'ACTIVO',
            ],
        ];

        $this->db->table('clientes')->insertBatch($data);
    }
}
