<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Productos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_producto'=>['type'=>'INT','auto_increment'=>true],
            'descripcion'=>['type'=>'VARCHAR','constraint'=>150],
            'precio_referencial'=>['type'=>'DECIMAL','constraint'=>'10,2'],
        ]);
        $this->forge->addKey('id_producto', true);
        $this->forge->createTable('productos');
    }

    public function down()
    {
        $this->forge->dropTable('productos');
    }
}
