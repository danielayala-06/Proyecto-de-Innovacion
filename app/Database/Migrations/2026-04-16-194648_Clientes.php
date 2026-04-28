<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Clientes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cliente' => [
                'type'=>'INT',
                'unsigned'=>true,
                'auto_increment'=>true
            ],
            'red_social' => [
                'type'=>'VARCHAR',
                'constraint'=>150,
                'null'=>true
            ],
            'id_persona' => [
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ]
            ,
            'id_empresa' => [
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>true,
            ],
            'metodo_comunicacion' => [
                'type'=>'ENUM',
                'constraint'=>['CORREO', 'WHATSAPP', 'LLAMADAS', 'OTRO'],
                'null'=>true,
            ],
            'estado' => [
                'type'=>'ENUM',
                'constraint'=>['ACTIVO', 'INACTIVO'],
                'default'=>'ACTIVO',
            ],
        ]);

        $this->forge->addKey('id_cliente', true);
        $this->forge->addForeignKey('id_persona','personas','id_persona','RESTRICT','RESTRICT');
        $this->forge->addForeignKey('id_empresa','empresas','id_empresa','SET NULL','RESTRICT');

        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}
