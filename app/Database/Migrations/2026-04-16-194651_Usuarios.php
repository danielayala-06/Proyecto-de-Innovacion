<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Usuarios extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_usuario' => ['type'=>'INT','auto_increment'=>true],
            'estado' => ['type'=>'VARCHAR','constraint'=>20],
            'nom_user' => ['type'=>'VARCHAR','constraint'=>50],
            'password' => ['type'=>'VARCHAR','constraint'=>255],
            'tipo_usuario' => ['type'=>'VARCHAR','constraint'=>50],
            'id_persona' => ['type'=>'INT'],
            'id_rol' => ['type'=>'INT'],
        ]);

        $this->forge->addKey('id_usuario', true);
        $this->forge->addForeignKey('id_persona','personas','id_persona','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_rol','roles','id_rol','CASCADE','CASCADE');

        $this->forge->createTable('usuarios');
    }

    public function down()
    {
        $this->forge->dropTable('usuarios');
    }
}
