<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableContratos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_contrato'          => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_cotizacion'       => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'observaciones'       => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
                'null'           => true,
            ]
        ]);
        // Creamos los atributos tipo DATE, DATETIME, ETC

        // Fecha donde se firma el contrato
        $this->forge->addField("fecha_contrato DATE NOT NULL DEFAULT CURRENT_DATE()");

        // Guarda la FECHA de INICIO del EVENTO(Matrimonio, Quino, Promocion, Etc.)
        $this->forge->addField("fecha_hora_inicio DATETIME NOT NULL");

        // Guarda la FECHA y hora del FINAL del EVENTO
        $this->forge->addField("fecha_hora_fin DATETIME NOT NULL");

        $this->forge->addKey('id_contrato', true);
        //
        $this->forge->addForeignKey('id_cotizacion','cotizaciones','id_cotizacion');

        $this->forge->createTable('contratos', true);
    }

    public function down()
    {
        $this->forge->dropTable('contratos', true);
    }
}
