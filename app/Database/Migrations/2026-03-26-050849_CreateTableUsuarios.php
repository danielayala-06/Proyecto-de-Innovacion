<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addField([
           'id_usuario' => [
               'type' => 'INT',
               'constraint' => 11,
               'unsigned' => true,
               'auto_increment' => true,
           ],
            'id_persona' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ]
        ]);
        $this->forge->addKey('id_usuario', true);
        $this->forge->addForeignKey('id_persona', 'personas', 'id_persona');
        $this->forge->createTable('usuarios');
    }

    public function down()
    {
        //
    }
}
