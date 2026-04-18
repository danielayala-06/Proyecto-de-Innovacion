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
                'unsigned'=>true
            ],
            'id_rol' => [
                'type'=>'INT',
                'unsigned'=>true
            ],
            'nombre_user' => [
                'type'=>'VARCHAR',
                'constraint'=>50
            ],
            'password' => [
                'type'=>'VARCHAR',
                'constraint'=>255
            ],
            'tipo_usuario' => [
                'type'=>'ENUM',
                'constraint'=>['ADMIN','INVITADO', 'PERSONALIZADO']
            ],
            'estado' => [
                'type'=>'ENUM',
                'constraint'=>['ACTIVO', 'INACTIVO']
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
