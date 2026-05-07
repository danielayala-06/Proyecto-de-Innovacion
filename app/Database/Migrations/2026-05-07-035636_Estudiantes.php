<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Estudiantes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_estudiante'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'id_apoderado'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'id_promocion'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'nombres'=>[
                'type'=>'VARCHAR',
                'constraint'=>30,
                'null'=>false
            ],
            'apellidos'=>[
                'type'=>'VARCHAR',
                'constraint'=>30,
                'null'=>false
            ],
            'fecha_nacimiento'=>[
                'type'=>'DATE',
                'null'=>true
            ],
            'color_fav'=>[
                'type'=>'VARCHAR',
                'constraint'=>30,
                'null'=>true
            ],
            'profesion_futura'=>[
                'type'=>'VARCHAR',
                'constraint'=>40,
                'null'=>true
            ],
        ]);
        $this->forge->addKey('id_estudiante', true);
        $this->forge->addForeignKey('id_apoderado','apoderados','id_apoderado','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_promocion','promociones_escolares','id_promocion','CASCADE','RESTRICT');
        $this->forge->createTable('estudiantes');
    }

    public function down()
    {
        $this->forge->dropTable('esudiantes');
    }
}
