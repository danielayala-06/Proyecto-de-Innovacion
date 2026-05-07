<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Contratos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_contrato'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'id_cotizacion'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'fecha_creacion'=>[
                'type'=>'DATE',
                'default'=>date('Y-m-d'),
                'null'=>false
            ],
            'fecha_emision'=>[
                'type'=>'DATE',
                'null'=>true
            ],
            // El adelanto es obligatorio para crear un contrato.
            'adelanto'=>[
                'type'=>'DECIMAL',
                'constraint'=>'7,2',
                'unsigned'=>true,
                'null'=>false
            ],
            'total'=>[
                'type'=>'DECIMAL',
                'constraint'=>'7,2',
                'unsigned'=>true,
                'null'=>false
            ],
            'observaciones'=>[
                'type'=>'TEXT',
                'null'=>true
            ],
            'estado'=>[
                'type'=>'ENUM',
                'constraint'=>['ACTIVO', 'CANCELADO', 'COMPLETADO'],
                'default'=>'ACTIVO'
            ],
        ]);
        $this->forge->addKey('id_contrato', true);
        $this->forge->addForeignKey('id_cotizacion','cotizaciones','id_cotizacion','RESTRICT','RESTRICT');
        $this->forge->createTable('contratos');
    }

    public function down()
    {
        $this->forge->dropTable('contratos');
    }
}
