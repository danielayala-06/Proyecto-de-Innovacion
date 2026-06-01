<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PromAlumnos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'promocion_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
            ],
            'completado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'enviado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addForeignKey('promocion_id', 'prom_promociones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('prom_alumnos');
    }

    public function down()
    {
        $this->forge->dropTable('prom_alumnos');
    }
}
