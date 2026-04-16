<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CotizacionesPaquetes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cotizacion_paquete'=>['type'=>'INT','auto_increment'=>true],
            'id_cotizacion'=>['type'=>'INT'],
            'id_paquete'=>['type'=>'INT'],
        ]);
        $this->forge->addKey('id_cotizacion_paquete', true);
        $this->forge->addForeignKey('id_cotizacion','cotizaciones','id_cotizacion','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_paquete','paquetes','id_paquete','CASCADE','CASCADE');
        $this->forge->createTable('cotizaciones_paquetes');
    }

    public function down()
    {
        $this->forge->dropTable('cotizaciones_paquetes');
    }
}
