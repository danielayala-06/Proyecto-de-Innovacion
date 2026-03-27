<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableCotizaciones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            // Primero agregamos las restricciones (PK, FK, UQ, etc.)
            'id_cotizacion'=> [
                'type'              => 'INT',
                'constraint'        => 11,
                'auto_increment'    => true,
                'unsigned'          => true,
                'null'              => false,
            ],
            'id_persona'=> [
                'type'              => 'INT',
                'constraint'        => 11,
                'unsigned'          => true,
                'null'              => false,
            ],
            'id_usuario'=> [
                'type'              => 'INT',
                'constraint'        => 11,
                'unsigned'          => true,
                'null'              => false,
            ],
            'id_empresa'=>[
                'type'              => 'INT',
                'constraint'        => 11,
                'unsigned'          => true,
                'null'              => false,
            ],
            'id_tipo_evento'=> [
                'type'              => 'INT',
                'constraint'        => 11,
                'unsigned'          => true,
                'null'              => false,
            ],
            'direccion'=> [
                'type'              => 'VARCHAR',
                'constraint'        => 100,
                'null'              => false,
            ],
            'referencia'=>[
                'type'              => 'VARCHAR',
                'constraint'        => 100,
                'null'              => false,
            ],
            'latitud'=>[
                'type'              => 'DECIMAL',
                'constraint'        => 8.6,
                'unsigned'          => false,
                'null'              => true,  
            ],
            'longintud'=>[
                'type'              => 'DECIMAL',
                'constraint'        => 8.6,
                'unsigned'          => false,
                'null'              => true,
            ]

        ]);

        $this->forge->addKey('id_cotizacion', true);

        $this->forge->addForeignKey('id_persona','personas','id_persona');
        $this->forge->addForeignKey('id_usuario','usuarios','id_usuario');
        $this->forge->addForeignKey('id_empresa','empresas','id_empresa');
        $this->forge->addForeignKey('id_tipo_evento','tipo_eventos','id_tipo_evento');

        // Agregamos la columna de fecha de creacion para la tabla Cotizaciones
        $this->forge->addField("fecha_creacion DATETIME NOT NULL DEFAULT current_timestamp()");
        
        // Agregamos la columna para los dias de vigencia de la cotizacion REVISAR!!!!
        $this->forge->addField("num_meses_vigencia DATE NOT NULL DEFAULT date_add(current_date(), INTERVAL 3 MONTH)");
        
        // Cuando se va a pasar de cotizacion a contrato REVISAR!!!!!!
        $this->forge->addField("fecha_registro DATE NULL");

        // Hora de INICIO del EVENTO
        $this->forge->addField("fecha_hora_inicio DATETIME NOT NULL");

        // Hora de FIN del EVENTO
        $this->forge->addField("fecha_hora_fin DATETIME NOT NULL");

        // Creamos la tabla
        $this->forge->createTable('cotizaciones', true);
    }

    public function down()
    {
        //
    }
}
