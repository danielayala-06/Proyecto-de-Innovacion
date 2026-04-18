<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Empresas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_empresa' => [
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'razon_social' => [
                'type'=>'VARCHAR',
                'constraint'=>150,
                'null'=>false
            ],
            'ruc' => [
                'type'=>'CHAR',
                'constraint'=>11,
                'null'=>false
            ],
            'nombre_comercial' => [
                'type'=>'VARCHAR',
                'constraint'=>150,
                'false'=>true
            ],
            'telefono' => [
                'type'=>'VARCHAR',
                'constraint'=>20,
                'null'=>true
            ],
            'correo' => [
                'type'=>'VARCHAR',
                'constraint'=>150,
                'null'=>true
            ],
        ]);
        $this->forge->addKey('id_empresa', true);
        $this->forge->addUniqueKey('ruc');
        $this->forge->createTable('empresas');
    }

    public function down()
    {
        $this->forge->dropTable('empresas');
    }
}
