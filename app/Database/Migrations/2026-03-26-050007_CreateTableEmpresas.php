<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableEmpresas extends Migration
{
    public function up()
    {
        $this->forge->addFields([
           'id_empresa'          => [
               'type'           => 'INT',
               'constraint'     => 11,
               'unsigned'       => true,
               'auto_increment' => true,
           ],
            'razon_social'       => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => false,
            ],
            'ruc'                =>[
                'type'           => 'VARCHAR',
                'constraint'     => '20',
                'null'           => false,
            ]
        ]);
        $this->forge->addKey('id_empresa', true);
        $this->forge->addUniqueKey('ruc');
        $this->forge->createTable('empresas');
    }

    public function down()
    {
        //
        $this->forge->dropTable('empresas');
    }
}
