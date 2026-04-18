<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Personas extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_persona' => [
                'type'=>'INT',
                'unsigned'=>true,
                'auto_increment'=>true
            ],
            'nombres' => [
                'type'=>'VARCHAR',
                'constraint'=>100,
                'null'=>false
            ],
            'apellidos' => [
                'type'=>'VARCHAR',
                'constraint'=>100,
                'null'=>true
            ],
            'telefono' => [
                'type'=>'CHAR',
                'constraint'=>9,
                'null'=>false
            ],
            'correo' => [
                'type'=>'VARCHAR',
                'constraint'=>150,
                'null'=>true
            ],
            'tel_alternativo' => [
                'type'=>'VARCHAR',
                'constraint'=>20,
                'null'=>true
            ],
            'numero_documento' => [
                'type'=>'VARCHAR',
                'constraint'=>50,
                'null'=>false
            ],
            'tipo_documento' => [
                'type'=>'ENUM',
                'constraint'=>['DNI', 'CE', 'PASAPORTE'],
                'null'=>false
            ],
        ]);
        $this->forge->addKey('id_persona', true);
        $this->forge->addUniqueKey('numero_documento');
        $this->forge->createTable('personas');
    }

    public function down()
    {
        $this->forge->dropTable('personas');
    }
}
