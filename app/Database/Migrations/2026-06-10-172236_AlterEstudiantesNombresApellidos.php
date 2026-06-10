<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterEstudiantesNombresApellidos extends Migration
{
    public function up(): void
    {
        // nombres: varchar(30) → varchar(100) para acomodar nombre completo fusionado
        // apellidos: NOT NULL → NULL (ya no se usa como campo separado)
        $this->db->query("ALTER TABLE estudiantes
            MODIFY COLUMN nombres   VARCHAR(100) NOT NULL,
            MODIFY COLUMN apellidos VARCHAR(100) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE estudiantes
            MODIFY COLUMN nombres   VARCHAR(30) NOT NULL,
            MODIFY COLUMN apellidos VARCHAR(30) NOT NULL");
    }
}
