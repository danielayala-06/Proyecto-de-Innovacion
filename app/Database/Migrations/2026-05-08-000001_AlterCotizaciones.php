<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCotizaciones extends Migration
{
    public function up()
    {
        // Agregar BORRADOR al ENUM de estado
        $this->forge->modifyColumn('cotizaciones', [
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['BORRADOR', 'PENDIENTE', 'APROBADA', 'RECHAZADA'],
                'default'    => 'BORRADOR',
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('cotizaciones', [
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['APROBADA', 'RECHAZADA', 'PENDIENTE'],
                'default'    => 'PENDIENTE',
                'null'       => false,
            ],
        ]);
    }
}
