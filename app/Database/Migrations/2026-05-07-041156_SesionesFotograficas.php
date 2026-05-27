<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SesionesFotograficas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_sesion'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'id_promocion'=>[
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'fecha_hora_sesion'=>[
                'type'=>'DATETIME',
                'null'=>false
            ],
            'tipo'=>[
                'type'=>'ENUM',
                'constraint'=>['exteriores','colegio','estudio', 'otro'],
                'default'=>'otro',
                'null'=>false
            ],
            'observaciones'=>[
                'type'=>'TEXT',
                'null'=>true
            ],
            'estado'=>[
                'type'=>'ENUM',
                'constraint'=>['pendiente','finalizado','cancelado'],
                'default'=>'pendiente',
                'null'=>false
            ],
        ]);
        $this->forge->addKey('id_sesion', true);
        $this->forge->addForeignKey('id_promocion','promociones_escolares','id_promocion','CASCADE','RESTRICT');
        $this->forge->createTable('sesiones_fotograficas');
    }

    public function down()
    {
        $this->forge->dropTable('sesiones_fotograficas');
    }
}
