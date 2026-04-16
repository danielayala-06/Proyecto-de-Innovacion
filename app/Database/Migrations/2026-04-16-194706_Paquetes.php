<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Paquetes extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_paquete'=>['type'=>'INT','auto_increment'=>true],
            'nombre_paquete'=>['type'=>'VARCHAR','constraint'=>150],
            'precio_base'=>['type'=>'DECIMAL','constraint'=>'10,2'],
            'imagen'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'detalle_paquete'=>['type'=>'TEXT','null'=>true],
            'estado'=>['type'=>'VARCHAR','constraint'=>20],
        ]);
        $this->forge->addKey('id_paquete', true);
        $this->forge->createTable('paquetes');
    }

    public function down()
    {
        $this->forge->dropTable('paquetes');
    }
}
