<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Contratos extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_contrato'=>['type'=>'INT','auto_increment'=>true],
            'fecha_contrato'=>['type'=>'DATE'],
            'observaciones'=>['type'=>'TEXT','null'=>true],
            'fecha_hora_inicio'=>['type'=>'DATETIME'],
            'fecha_hora_fin'=>['type'=>'DATETIME'],
            'id_cotizacion'=>['type'=>'INT'],
            'estado'=>['type'=>'VARCHAR','constraint'=>20],
            'total_final'=>['type'=>'DECIMAL','constraint'=>'10,2'],
        ]);
        $this->forge->addKey('id_contrato', true);
        $this->forge->addForeignKey('id_cotizacion','cotizaciones','id_cotizacion','CASCADE','CASCADE');
        $this->forge->createTable('contratos');
    }

    public function down()
    {
        $this->forge->dropTable('contratos');
    }
}
