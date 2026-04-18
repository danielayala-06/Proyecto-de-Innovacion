<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaquetesServicios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_servicio'=>[
                'type'=>'INT',
                'unsigned'=>true
            ],
            'id_paquete'=>[
                'type'=>'INT',
                'unsigned'=>true
            ],
        ]);
        $this->forge->addKey(['id_servicio', 'id_paquete'], true);
        $this->forge->addForeignKey('id_servicio','servicios','id_servicio','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_paquete','paquetes','id_paquete','CASCADE','RESTRICT');
        $this->forge->createTable('paquetes_servicios');
    }

    public function down()
    {
        $this->forge->dropTable('paquetes_servicios');
    }
}
