<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Paquetes extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_paquete'=>
            [
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true,
            ],
            'nombre_paquete'=>
            [
                'type'=>'VARCHAR',
                'constraint'=>150
            ],
            'categoria'=>
            [
                'type'=>'ENUM',
                'constraint'=>['CUADROS', 'ANUARIOS', 'QUINOS', 'SESIONES', 'OTROS'],
                'null'=>false,
            ],
            'precio_base'=>
            [
                'type'=>'DECIMAL',
                'constraint'=>'10,2',
                'unsigned'=>true
            ],
            'imagen'=>
            [
                'type'=>'VARCHAR',
                'constraint'=>255,
                'null'=>true
            ],
            'descripcion'=>
            [
                'type'=>'TEXT',
                'null'=>true
            ],
            'estado'=>
            [
                'type'=>'ENUM',
                'constraint'=>['ACTIVO','INACTIVO'],
                'default'=>'ACTIVO'
            ]
        ]);
        $this->forge->addKey('id_paquete', true);
        $this->forge->createTable('paquetes');
    }

    public function down()
    {
        $this->forge->dropTable('paquetes');
    }
}
