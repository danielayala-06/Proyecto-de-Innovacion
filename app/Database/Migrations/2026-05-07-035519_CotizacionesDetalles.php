<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CotizacionesDetalles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_detalle' => [
                'type'=>'INT',
                'unsigned'=>true,
                'auto_increment'=>true
            ],
            'id_cotizacion' => [
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'tipo_item' => [
                'type'=>'ENUM',
                'constraint'=>['paquete','producto', 'personalizado'],
                'default'=>'paquete',
                'null'=>false
            ],
            'id_referencia' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
            ],
            'descripcion' => [
                'type'=>'TEXT',
                'null'=>true
            ],
            'cantidad' => [
                'type'     => 'SMALLINT',
                'unsigned' => true,
                'default'  => 1,
                'null'     => false,
            ],
            'precio_unitario' => [
                'type'=>'DECIMAL',
                'constraint'=>'7,2',
                'unsigned'=>true,
                'null'=>false
            ],
        ]);

        $this->forge->addKey('id_detalle', true);
        $this->forge->addForeignKey('id_cotizacion','cotizaciones','id_cotizacion','CASCADE','RESTRICT');

        $this->forge->createTable('cotizaciones_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('cotizaciones_detalles');
    }
}
