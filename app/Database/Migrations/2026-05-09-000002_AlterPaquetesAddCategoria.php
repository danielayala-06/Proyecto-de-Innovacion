<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPaquetesAddCategoria extends Migration
{
    public function up()
    {
        $this->forge->addColumn('paquetes', [
            'categoria' => [
                'type'       => 'ENUM',
                'constraint' => ['Cuadros', 'Anuarios', 'otros'],
                'null'       => true,
                'default'    => null,
                'after'      => 'nivel_disponible',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('paquetes', 'categoria');
    }
}
