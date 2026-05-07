<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Colegios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_colegio'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'nombre_colegio'=>[
                'type'=>'VARCHAR',
                'constraint'=>100,
                'null'=>false
            ],
            'distrito'=>[
                'type'=>'VARCHAR',
                'constraint'=>100,
                'null'=>false,
            ],
            'provincia'=>[
                'type'=>'VARCHAR',
                'constraint'=>100,
                'null'=>false,
            ],
            'estado'=>[
                'type'=>'ENUM',
                'constraint'=>["ACTIVO","INACTIVO"],
                'default'=>"ACTIVO",
                'null'=>false
            ]
        ]);
        $this->forge->addKey('id_colegio', true);
        $this->forge->createTable('colegios');
    }

    public function down()
    {
        $this->forge->dropTable('colegios');
    }
}
