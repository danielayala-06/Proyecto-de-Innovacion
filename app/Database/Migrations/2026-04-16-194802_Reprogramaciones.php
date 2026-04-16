<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Reprogramaciones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_reprogramacion'=>['type'=>'INT','auto_increment'=>true],
            'fecha_anterior'=>['type'=>'DATETIME'],
            'fecha_nueva'=>['type'=>'DATETIME'],
            'motivo'=>['type'=>'TEXT'],
            'fecha_cambio'=>['type'=>'DATETIME'],
            'id_contrato'=>['type'=>'INT'],
        ]);
        $this->forge->addKey('id_reprogramacion', true);
        $this->forge->addForeignKey('id_contrato','contratos','id_contrato','CASCADE','CASCADE');
        $this->forge->createTable('reprogramaciones');
    }

    public function down()
    {
        $this->forge->dropTable('reprogramaciones');
    }
}
