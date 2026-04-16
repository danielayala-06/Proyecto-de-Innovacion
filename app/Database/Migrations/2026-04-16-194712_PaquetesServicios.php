<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaquetesServicios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_paquete_servicio'=>['type'=>'INT','auto_increment'=>true],
            'id_servicio'=>['type'=>'INT'],
            'id_paquete'=>['type'=>'INT'],
        ]);
        $this->forge->addKey('id_paquete_servicio', true);
        $this->forge->addForeignKey('id_servicio','servicios','id_servicio','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_paquete','paquetes','id_paquete','CASCADE','CASCADE');
        $this->forge->createTable('paquetes_servicios');
    }

    public function down()
    {
        $this->forge->dropTable('paquetes_servicios');
    }
}
