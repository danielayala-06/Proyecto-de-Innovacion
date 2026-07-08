<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropProductosTamanio extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('productos', 'tamanio');
    }

    public function down(): void
    {
        $this->forge->addColumn('productos', [
            'tamanio' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'detalle',
            ],
        ]);
    }
}
