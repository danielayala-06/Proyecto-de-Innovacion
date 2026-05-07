<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PromocionesEscolares extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_promocion' => [
                'type'=>'INT',
                'unsigned'=>true,
                'auto_increment'=>true
            ],
            'id_colegio' => [
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'id_cotizacion' => [
                'type'=>'INT',
                'unsigned'=>true,
                'null'=>false
            ],
            'nombre' => [
                'type'=>'VARCHAR',
                'constraint'=>100,
                'null'=>false
            ],
            'grado' => [
                'type'=>'VARCHAR',
                'constraint'=>10,
                'null'=>false
            ],
            'seccion' => [
                'type'=>'VARCHAR',
                'constraint'=>10,
                'null'=>true
            ],
            'num_estudiantes' => [
                'type'=>'TINYINT',
                'unsigned'=>true,
                'null'=>false
            ],
            'anio' => [
                'type'=>'YEAR',
                'default'=>date('Y'),
                'null'=>false
            ],
            'is_active' => [
                'type'=>'boolean',
                'default'=>true,
                'null'=>false,
            ]
        ]);

        $this->forge->addKey('id_promocion', true);
        $this->forge->addForeignKey('id_colegio','colegios','id_colegio','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_cotizacion','cotizaciones','id_cotizacion','CASCADE','RESTRICT');

        $this->forge->createTable('promociones_escolares');
    }

    public function down()
    {
        $this->forge->dropTable('promociones_escolares');
    }
}
