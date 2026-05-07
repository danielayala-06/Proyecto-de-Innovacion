<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Usuarios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_usuario' => [
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'id_persona' => [
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'id_rol' => [
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'nombre_user' => [
                'type'=>'VARCHAR',
                'constraint'=>50,
                'null'=>false,
            ],
            'password_hash' => [
                'type'=>'VARCHAR',
                'constraint'=>255,
                'null'=>false
            ],
            'estado' => [
                'type'=>'ENUM',
                'constraint'=>['ACTIVO', 'INACTIVO'],
                'default'=>'ACTIVO',
                'null'=>false
            ],
        ]);

        $this->forge->addKey('id_usuario', true);
        $this->forge->addUniqueKey('nombre_user');
        $this->forge->addForeignKey('id_persona','personas','id_persona','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_rol','roles','id_rol','CASCADE','RESTRICT');

        $this->forge->createTable('usuarios');
    }

    public function down()
    {
        $this->forge->dropTable('usuarios');
    }
}
