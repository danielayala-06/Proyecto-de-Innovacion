<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Cotizaciones extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_cotizacion'=>['type'=>'INT','auto_increment'=>true],
            'num_dias_vigencia'=>['type'=>'INT'],
            'fecha_registro'=>['type'=>'DATE'],
            'fecha_hora_inicio'=>['type'=>'DATETIME'],
            'fecha_hora_fin'=>['type'=>'DATETIME'],
            'direccion'=>['type'=>'VARCHAR','constraint'=>255],
            'referencia'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'latitud'=>['type'=>'DECIMAL','constraint'=>'10,8'],
            'longitud'=>['type'=>'DECIMAL','constraint'=>'11,8'],
            'estado'=>['type'=>'VARCHAR','constraint'=>20],
            'total_estimado'=>['type'=>'DECIMAL','constraint'=>'10,2'],
            'id_cliente'=>['type'=>'INT'],
            'id_usuario'=>['type'=>'INT'],
        ]);
        $this->forge->addKey('id_cotizacion', true);
        $this->forge->addForeignKey('id_cliente','clientes','id_cliente','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_usuario','usuarios','id_usuario','CASCADE','CASCADE');
        $this->forge->createTable('cotizaciones');
    }

    public function down()
    {
        $this->forge->dropTable('cotizaciones');
    }
}
