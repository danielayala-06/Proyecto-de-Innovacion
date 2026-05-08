<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCotizaciones extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('cotizaciones', [
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['PENDIENTE', 'APROBADA', 'RECHAZADA'],
                'default'    => 'PENDIENTE',
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
