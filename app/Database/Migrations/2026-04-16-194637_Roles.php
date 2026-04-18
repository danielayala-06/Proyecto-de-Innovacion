<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Roles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_rol' => [
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'rol' => [
                'type'=>'VARCHAR',
                'constraint'=>100,
                'null'=>false
            ],
            'estado' => [
                'type'=>'ENUM',
                'constraint'=>['ACTIVO','INACTIVO'],
                'default'=>'ACTIVO'
            ],
        ]);
        $this->forge->addKey('id_rol', true);
        $this->forge->addUniqueKey('rol');
        $this->forge->createTable('roles');
    }

    public function down()
    {
        $this->forge->dropTable('roles');
    }
}
