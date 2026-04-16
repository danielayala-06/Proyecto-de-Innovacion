<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Clientes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cliente' => ['type'=>'INT','auto_increment'=>true],
            'red_social' => ['type'=>'VARCHAR','constraint'=>100,'null'=>true],
            'id_persona' => ['type'=>'INT'],
            'id_empresa' => ['type'=>'INT','null'=>true],
        ]);

        $this->forge->addKey('id_cliente', true);
        $this->forge->addForeignKey('id_persona','personas','id_persona','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_empresa','empresas','id_empresa','SET NULL','CASCADE');

        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}
