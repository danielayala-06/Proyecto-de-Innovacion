<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddArchivadoToCotizacionesContratos extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE cotizaciones ADD COLUMN archivado TINYINT(1) NOT NULL DEFAULT 0');
        $this->db->query('ALTER TABLE contratos    ADD COLUMN archivado TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE cotizaciones DROP COLUMN archivado');
        $this->db->query('ALTER TABLE contratos    DROP COLUMN archivado');
    }
}
