<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClientesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('clientes')->insertBatch([
             [
                 'id_persona'          => 1, // Carlos Mendoza
                 'id_empresa'          => null,
                 'red_social'          => '@carlosmendoza_ig',
                 'metodo_comunicacion' => 'WhatsApp',
                 'tipo_cliente'        => 'RECURRENTE',
                 'estado'              => 'ACTIVO',
             ],
             [
                 'id_persona'          => 2, // Lucía Torres
                 'id_empresa'          => 1, // Eventos Mágicos
                 'red_social'          => 'lucia.torres',
                 'metodo_comunicacion' => 'Correo',
                 'tipo_cliente'        => 'LEAL',
                 'estado'              => 'ACTIVO',
             ],
             [
                 'id_persona'          => 3, // Roberto Sánchez
                 'id_empresa'          => 2, // Lima Norte Corp
                 'red_social'          => null,
                 'metodo_comunicacion' => 'Teléfono',
                 'tipo_cliente'        => 'NUEVO',
                 'estado'              => 'ACTIVO',
             ],
             [
                 'id_persona'          => 4, // Mariana Flores
                 'id_empresa'          => null,
                 'red_social'          => '@mariana.flores',
                 'metodo_comunicacion' => 'WhatsApp',
                 'tipo_cliente'        => 'NUEVO',
                 'estado'              => 'ACTIVO',
             ],
        ]);
    }
}
