<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega token_compartido a prom_promociones.
 * Es el token del link único por promoción que los alumnos usan para auto-registrarse.
 */
class TokenCompartidoPromocion extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('prom_promociones', [
            'token_compartido' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
                'after'      => 'id_promocion_escolar',
            ],
        ]);

        $this->db->query('ALTER TABLE prom_promociones ADD UNIQUE KEY uq_token_compartido (token_compartido)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE prom_promociones DROP INDEX uq_token_compartido');
        $this->forge->dropColumn('prom_promociones', 'token_compartido');
    }
}
