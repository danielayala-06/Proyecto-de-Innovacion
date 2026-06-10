<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Elimina el UNIQUE KEY en contratos.id_cotizacion para permitir que una
 * cotización pueda generar un nuevo contrato si el anterior fue CANCELADO.
 * La restricción de unicidad del contrato activo se mantiene a nivel de
 * servicio (sólo puede existir un contrato ACTIVO por cotización).
 */
class DropUniqueIdCotizacionFromContratos extends Migration
{
    public function up(): void
    {
        // MySQL no permite eliminar el índice único mientras haya una FK que lo use.
        // Pasos: bajar FK → bajar UNIQUE → crear índice regular → restaurar FK.
        $this->db->query('ALTER TABLE contratos DROP FOREIGN KEY contratos_id_cotizacion_foreign');
        $this->db->query('ALTER TABLE contratos DROP INDEX id_cotizacion');
        $this->db->query('ALTER TABLE contratos ADD INDEX idx_cotizacion (id_cotizacion)');
        $this->db->query('ALTER TABLE contratos ADD CONSTRAINT contratos_id_cotizacion_foreign FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones(id_cotizacion) ON DELETE RESTRICT ON UPDATE RESTRICT');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE contratos DROP FOREIGN KEY contratos_id_cotizacion_foreign');
        $this->db->query('ALTER TABLE contratos DROP INDEX idx_cotizacion');
        $this->db->query('ALTER TABLE contratos ADD UNIQUE KEY id_cotizacion (id_cotizacion)');
        $this->db->query('ALTER TABLE contratos ADD CONSTRAINT contratos_id_cotizacion_foreign FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones(id_cotizacion) ON DELETE RESTRICT ON UPDATE RESTRICT');
    }
}
