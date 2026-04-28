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
                'unsigned'=>true
            ],
            'id_usuario'=>[
                'type'=>'INT',
                'unsigned'=>true
            ],
            'nombre_cotizacion'=>[
                'type'=>'VARCHAR',
                'constraint'=>100,
                'null'=>false
            ],
            'num_dias_vigencia'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'default'=>7,
            ],
            'fecha_registro'=>[
                'type'=>'DATE',
                'default'=>date('Y-m-d')
            ],
            'fecha_hora_inicio'=>[
                'type'=>'DATETIME',
                'null'=>true
            ],
            'fecha_hora_fin'=>[
                'type'=>'DATETIME',
                'null'=>true
            ],
            'direccion'=>[
                'type'=>'VARCHAR',
                'constraint'=>255,
                'null'=>true
            ],
            'referencia'=>[
                'type'=>'VARCHAR',
                'constraint'=>255,
                'null'=>true
            ],
            'latitud'=>[
                'type'=>'DECIMAL',
                'constraint'=>'8,6',
                'null'=>true
            ],
            'longitud'=>[
                'type'=>'DECIMAL',
                'constraint'=>'8,6',
                'null'=>true
            ],
            'observaciones'=>[
                'type'=>'TEXT',
                'null'=>true
            ],
            'total_estimado'=>[
                'type'=>'DECIMAL',
                'constraint'=>'10,2',
                'unsigned'=>true
            ],
            'estado'=>[
                'type'=>'ENUM',
                'constraint'=>['APROBADA', 'RECHAZADA', 'PENDIENTE'],
                'default'=>'PENDIENTE'
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
