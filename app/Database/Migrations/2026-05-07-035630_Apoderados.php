<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Apoderados extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_apoderado'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'id_persona'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'tipo_relacion'=>[
                'type'=>'ENUM',
                'constraint'=>['padre','madre', 'hermano','otro'],
                'default'=>'madre',
                'null'=>false
            ],
        ]);
        $this->forge->addKey('id_apoderado', true);
        $this->forge->addForeignKey('id_persona','personas','id_persona','CASCADE','RESTRICT');
        $this->forge->createTable('apoderados');
    }

    public function down()
    {
        $this->forge->dropTable('apoderados');
    }
}
