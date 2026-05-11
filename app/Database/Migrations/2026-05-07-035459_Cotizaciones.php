<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Cotizaciones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cotizacion'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'id_cliente'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'id_usuario'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'fecha_registro'=>[
                'type'=>'DATE',
                'default'=>date('Y-m-d'),
                'null'=>false
            ],
            'observaciones'=>[
                'type'=>'TEXT',
                'null'=>true
            ],
            'total_estimado'=>[
                'type'=>'DECIMAL',
                'constraint'=>'7,2',
                'unsigned'=>true,
                'null'=>false
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['PENDIENTE', 'APROBADA', 'RECHAZADA'],
                'default'    => 'PENDIENTE',
                'null'       => false,
            ],
        ]);
        $this->forge->addKey('id_cotizacion', true);
        $this->forge->addForeignKey('id_cliente','clientes','id_cliente','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_usuario','usuarios','id_usuario','CASCADE','CASCADE');
        $this->forge->createTable('cotizaciones');
    }

    public function down()
    {
        $this->forge->dropTable('cotizaciones');
    }
}
