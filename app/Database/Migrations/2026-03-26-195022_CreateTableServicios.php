<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableServicios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_servicios'=>[
                'type'              => 'INT',
                'constraint'        => 11,
                'auto_increment'    => true,
                'null'              => false
            ],
            'nombre' =>[
                'type'              => 'VARCHAR',
                'constraint'        => '50',
                'null'              => false
            ]
        ]);

        $this->forge->addKey('id_servicios', true);

        $this->forge->createTable('servicios');

    }

    public function down()
    {
        $this->forge->dropTable('servicios');
    }
}
