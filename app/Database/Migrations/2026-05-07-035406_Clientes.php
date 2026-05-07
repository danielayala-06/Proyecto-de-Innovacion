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
            'id_persona' => [
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'red_social' => [
                'type'=>'VARCHAR',
                'constraint'=>150,
                'null'=>true
            ],
            'metodo_comunicacion' => [
                'type'=>'ENUM',
                'constraint'=>['correo', 'whatsapp', 'llamada', 'otro'],
                'default'=>'whatsapp',
                'null'=>true,
            ],
            'acepta_promociones' => [
                'type'=>'boolean',
                'default'=>false,
                'null'=>false,
            ],
            'estado' => [
                'type'=>'ENUM',
                'constraint'=>['ACTIVO', 'INACTIVO'],
                'default'=>'ACTIVO',
                'null'=>false,
            ],
        ]);

        $this->forge->addKey('id_cliente', true);
        $this->forge->addForeignKey('id_persona','personas','id_persona','RESTRICT','RESTRICT');

        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}
