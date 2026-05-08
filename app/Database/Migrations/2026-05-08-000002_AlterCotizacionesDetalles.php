<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCotizacionesDetalles extends Migration
{
    public function up()
    {
        // Agregar 'personalizado' al ENUM de tipo_item
        $this->forge->modifyColumn('cotizaciones_detalles', [
            'tipo_item' => [
                'type'       => 'ENUM',
                'constraint' => ['paquete', 'producto', 'personalizado'],
                'default'    => 'paquete',
                'null'       => false,
            ],
        ]);

        // Hacer id_referencia nullable (ítems personalizados no tienen referencia)
        $this->forge->modifyColumn('cotizaciones_detalles', [
            'id_referencia' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('cotizaciones_detalles', [
            'tipo_item' => [
                'type'       => 'ENUM',
                'constraint' => ['paquete', 'producto'],
                'default'    => 'paquete',
                'null'       => false,
            ],
        ]);

        $this->forge->modifyColumn('cotizaciones_detalles', [
            'id_referencia' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => false,
            ],
        ]);
    }
}
