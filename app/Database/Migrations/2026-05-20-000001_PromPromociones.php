<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PromPromociones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'colegio_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'nivel' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'cuadros_total' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'cuadros_usados' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'anuarios_total' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'anuarios_usados' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'activa' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('colegio_id', 'colegios', 'id_colegio', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('prom_promociones');
    }

    public function down()
    {
        $this->forge->dropTable('prom_promociones');
    }
}
