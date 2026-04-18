<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Productos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_producto'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'nombre'=>[
                'type'=>'VARCHAR',
                'constraint'=>150,
                'null'=>false
            ],
            'detalle'=>[
                'type'=>'TEXT',
                'null'=>true
            ],
            'tamanio'=>[
                'type'=>'VARCHAR',
                'constraint'=>150,
                'null'=>true
            ],
            'unidad'=>[
                'type'=>'VARCHAR',
                'constraint'=>50,
                'null'=>false
            ],
            'estado'=>[
                'type'=>'ENUM',
                'constraint'=>['ACTIVO','INACTIVO'],
                'default'=>'ACTIVO'
            ],
        ]);

        $this->forge->addKey('id_producto', true);
        $this->forge->createTable('productos');
    }

    public function down()
    {
        $this->forge->dropTable('productos');
    }
}
