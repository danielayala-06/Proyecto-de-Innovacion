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
                'unsigned'=>true
            ],
            'fecha_contrato'=>[
                'type'=>'DATE',
                'default'=>date('Y-m-d')
            ],
            'fecha_emision_contrato'=>[
                'type'=>'DATE',
                'null'=>true
            ],
            // El adelanto es obligatorio para crear un contrato.
            'adelanto'=>[
                'type'=>'DECIMAL',
                'constraint'=>'10,2',
                'unsigned'=>true,
            ],
            'observaciones'=>[
                'type'=>'TEXT',
                'null'=>true
            ],
            'fecha_hora_inicio'=>[
                'type'=>'DATETIME'
            ],
            'fecha_hora_fin'=>[
                'type'=>'DATETIME'
            ],
            'estado'=>[
                'type'=>'ENUM',
                'constraint'=>['ACTIVO', 'CANCELADO', 'COMPLETADO'],
                'default'=>'ACTIVO'
            ],
            'total_final'=>[
                'type'=>'DECIMAL',
                'constraint'=>'10,2',
                'unsigned'=>true,
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
