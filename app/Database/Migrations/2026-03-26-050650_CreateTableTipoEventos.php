<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableTipoEventos extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_tipo_evento' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nombre_tipo_evento' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ]
        ]);
        $this->forge->addKey('id_tipo_evento', true);
        $this->forge->createTable('tipo_eventos');
    }

    public function down()
    {
        //
        $this->forge->dropTable('tipo_eventos');
    }
}

