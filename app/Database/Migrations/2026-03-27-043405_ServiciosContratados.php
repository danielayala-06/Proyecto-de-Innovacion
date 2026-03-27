<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ServiciosContratados extends Migration
{
    public function up()
    {
        $this->forge->addField([
           'id_servicio_contratado' => [
               'type' => 'INT',
               'constraint' => 11,
               'unsigned' => true,
               'auto_increment' => true,
           ],
            'id_servicio' =>[
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_cotizacion' =>[
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'cantidad' =>[
                'type' => 'TINYINT',
                'constraint' => 5,
                'unsigned' => true,
                'null' => false,
            ],
            'precio' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'unsigned' => true,
                'null' => false,
            ]
        ]);

        // PRIMARY KEY
        $this->forge->addKey('id_servicio_contratado', true);

        // FORANEA con la TABLA SERVICIOS
        $this->forge->addForeignKey('id_servicio', 'servicios', 'id_servicio');

        // FORANEA con la TABLA COTIZACIONES
        $this->forge->addForeignKey('id_cotizacion', 'cotizaciones', 'id_cotizacion');

        //
        $this->forge->createTable('servicios_contratados');
    }

    public function down()
    {
        $this->forge->dropTable('servicios_contratados');
    }
}
