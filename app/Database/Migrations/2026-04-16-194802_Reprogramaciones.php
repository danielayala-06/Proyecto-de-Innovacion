<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Reprogramaciones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_reprogramacion'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true,
            ],
            'id_contrato'=>[
                'type'=>'INT',
                'unsigned'=>true,
            ],
            'fecha_anterior'=>[
                'type'=>'DATETIME',
                'null'=>false,
            ],
            'fecha_nueva'=>[
                'type'=>'DATETIME',
                'null'=>false,
            ],
            'motivo'=>[
                'type'=>'TEXT',
                'null'=>true,
            ],
            'fecha_cambio'=>[
                'type'=>'DATETIME',
                'default'=>date('Y-m-d H:i:s'),
            ],
            //Fecha limite para usar el adelanto en otra fecha(30 dias desde fecha cambio)
            'fecha_limite_aplicacion'=>[
                'type'=>'DATE',
                'null'=>true,
            ],
        ]);
        $this->forge->addKey('id_reprogramacion', true);
        $this->forge->addForeignKey('id_contrato','contratos','id_contrato','RESTRICT','RESTRICT');
        $this->forge->createTable('reprogramaciones');
    }

    public function down()
    {
        $this->forge->dropTable('reprogramaciones');
    }
}
