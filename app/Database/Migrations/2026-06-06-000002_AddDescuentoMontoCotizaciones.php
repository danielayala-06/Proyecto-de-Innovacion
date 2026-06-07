<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescuentoMontoCotizaciones extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('cotizaciones', [
            'descuento_monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '7,2',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'total_estimado',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('cotizaciones', 'descuento_monto');
    }
}
