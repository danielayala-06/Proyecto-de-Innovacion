<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaquetesProductos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_paquete_prod'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'auto_increment'=>true
            ],
            'id_paquete'=>
            [
                'type'=>'INT',
                'unsigned'=>true,
            ],
            'id_producto'=>
            [
                'type'=>'INT',
                'unsigned'=>true,
            ],
            'cantidad'=>
            [
                'type'=>'TINYINT',
                'unsigned'=>true,
                'default'=>1
            ]
        ]);
        $this->forge->addKey('id_paquete_prod', true);
        $this->forge->addForeignKey('id_producto','productos','id_producto','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_paquete','paquetes','id_paquete','CASCADE','CASCADE');
        $this->forge->createTable('paquetes_productos');
    }

    public function down()
    {
        $this->forge->dropTable('paquetes_productos');
    }
}
