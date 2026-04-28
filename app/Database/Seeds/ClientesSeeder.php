<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClientesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('clientes')->insertBatch([
             [
                 'id_persona'          => 10, // ARELLA JSOFDJALKSDJF;LA
                 'id_empresa'          => null,
                 'red_social'          => null,
                 'metodo_comunicacion' => 'WhatsApp',
                 'estado'              => 'ACTIVO',
             ],
             [
                 'id_persona'          => 2, // Lucía Torres
                 'id_empresa'          => 1, // Eventos Mágicos
                 'red_social'          => 'lucia.torres',
                 'metodo_comunicacion' => 'Correo',
                 'estado'              => 'ACTIVO',
             ],
             [
                 'id_persona'          => 3, // Roberto Sánchez
                 'id_empresa'          => 2, // Lima Norte Corp
                 'red_social'          => null,
                 'metodo_comunicacion' => 'Teléfono',
                 'estado'              => 'ACTIVO',
             ],
             [
                 'id_persona'          => 4, // Mariana Flores
                 'id_empresa'          => null,
                 'red_social'          => '@mariana.flores',
                 'metodo_comunicacion' => 'WhatsApp',
                 'estado'              => 'ACTIVO',
             ],
        ]);
    }
}
