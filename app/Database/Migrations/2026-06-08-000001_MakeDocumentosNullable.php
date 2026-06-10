<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeDocumentosNullable extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE personas
            MODIFY numero_documento VARCHAR(50)                    NULL DEFAULT NULL,
            MODIFY tipo_documento   ENUM(\'DNI\',\'CE\',\'PASAPORTE\') NULL DEFAULT NULL
        ');
    }

    public function down(): void
    {
        $this->db->query("UPDATE personas SET numero_documento = CONCAT('SIN-', id_persona) WHERE numero_documento IS NULL");
        $this->db->query("UPDATE personas SET tipo_documento = 'DNI' WHERE tipo_documento IS NULL");
        $this->db->query('ALTER TABLE personas
            MODIFY numero_documento VARCHAR(50)                    NOT NULL,
            MODIFY tipo_documento   ENUM(\'DNI\',\'CE\',\'PASAPORTE\') NOT NULL
        ');
    }
}
