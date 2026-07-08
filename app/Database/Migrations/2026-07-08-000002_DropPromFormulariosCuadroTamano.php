<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropPromFormulariosCuadroTamano extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('prom_formularios', 'cuadro_tamano');
    }

    public function down(): void
    {
        $this->forge->addColumn('prom_formularios', [
            'cuadro_tamano' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'tiene_cuadro',
            ],
        ]);
    }
}
