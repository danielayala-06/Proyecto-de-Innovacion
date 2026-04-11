<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CotizacionesPruebas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cotizacion'  => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
                'unsigned'       => true,
            ],
            'dni' =>[
                'type'           => 'CHAR',
                'constraint'     => 8,
                'null'           => false,
            ],
            'cliente'=>[
                'type'           => 'VARCHAR',
                'constraint'     => 100,
                'null'           => false,
            ],
            'paquete'=>[
                'type'           => 'VARCHAR',
                'constraint'     => 100,
                'null'           => false,
            ],
            'fecha_evento'=>[
                'type'           => 'DATE',
                'null'           => false,
            ],
            'monto_acordado'=>[
                'type'           => 'DECIMAL',
                'constraint'     => '8.2',
                'unsigned'       => true,
                'null'           => false,
            ],
            'estado'=>[
                'type'           => 'ENUM',
                'constraint'     => ['VIGENTE','VENCIDO'],
                'default'        => 'VIGENTE',
                'null'           => false,
            ],
            'created_at'=>[
                'type'           => 'DATETIME',
                'null'           => false,
                'default'        => date('Y-m-d H:i'),
            ],
            'updated_at'=>[
                'type'           => 'DATETIME',
                'null'           => true,
            ]
        ]);
        // Restricciones
        $this->forge->addKey('id_cotizacion', true);
        $this->forge->addKey('dni', false, true);

        // Creamos la tabla
        $this->forge->createTable('cotizacion_pruebas');
    }

    public function down()
    {
        $this->forge->dropTable('cotizacion_pruebas');
    }
}
