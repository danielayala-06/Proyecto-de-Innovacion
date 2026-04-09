<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableServicios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_servicio'=>[
                'type'              => 'INT',
                'constraint'        => 11,
                'unsigned'          => true,
                'auto_increment'    => true,
                'null'              => false
            ],
            'nombre' =>[
                'type'              => 'VARCHAR',
                'constraint'        => 50,
                'null'              => false
            ]
        ]);

        $this->forge->addPrimaryKey('id_servicio');

        $this->forge->createTable('servicios', true);

    }

    public function down()
    {
        $this->forge->dropTable('servicios');
    }
}
