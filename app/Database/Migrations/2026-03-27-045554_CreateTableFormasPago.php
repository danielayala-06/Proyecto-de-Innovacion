<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableFormasPago extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_forma_pago' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'forma_pago' => [
                'type' => 'VARCHAR',
                'constraint' => '70',
                'null' => false,
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ]
        ]);
        $this->forge->addPrimaryKey('id_forma_pago');
        $this->forge->createTable('formas_pago');
    }

    public function down()
    {
        //
        $this->forge->dropTable('formas_pago');
    }
}
