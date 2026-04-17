<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Servicios extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_servicio'=>['type'=>'INT','auto_increment'=>true],
            'nombre_servicio'=>['type'=>'VARCHAR','constraint'=>150],
            'detalle_servicio'=>['type'=>'TEXT','null'=>true],
            'estado'=>['type'=>'VARCHAR','constraint'=>20],
        ]);
        $this->forge->addKey('id_servicio', true);
        $this->forge->createTable('servicios');
    }

    public function down()
    {
        $this->forge->dropTable('servicios');
    }
}
