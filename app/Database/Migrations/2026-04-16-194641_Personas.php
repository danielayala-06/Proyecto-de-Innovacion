<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Personas extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_persona' => ['type'=>'INT','auto_increment'=>true],
            'nombres' => ['type'=>'VARCHAR','constraint'=>100],
            'apellidos' => ['type'=>'VARCHAR','constraint'=>100],
            'telefono' => ['type'=>'VARCHAR','constraint'=>20,'null'=>true],
            'correo' => ['type'=>'VARCHAR','constraint'=>150],
            'tel_alternativo' => ['type'=>'VARCHAR','constraint'=>20,'null'=>true],
            'numero_documento' => ['type'=>'VARCHAR','constraint'=>50],
            'tipo_documento' => ['type'=>'VARCHAR','constraint'=>20],
        ]);
        $this->forge->addKey('id_persona', true);
        $this->forge->createTable('personas');
    }

    public function down()
    {
        $this->forge->dropTable('personas');
    }
}
