<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorFormaPagoToVarchar extends Migration
{
    public function up(): void
    {
        // 1. Add new varchar column
        $this->db->query('ALTER TABLE pagos ADD COLUMN forma_pago VARCHAR(60) NULL DEFAULT NULL AFTER id_contrato');

        // 2. Copy existing data from the join
        $this->db->query('
            UPDATE pagos p
            LEFT JOIN formas_pago fp ON fp.id_form_pago = p.id_form_pago
            SET p.forma_pago = fp.nombre_forma_pago
        ');

        // 3. Drop FK from the nullable migration (2026-06-09-000002)
        $this->db->query('ALTER TABLE pagos DROP FOREIGN KEY pagos_id_form_pago_foreign');

        // 4. Drop the old integer column
        $this->db->query('ALTER TABLE pagos DROP COLUMN id_form_pago');

        // 5. Drop the now-orphaned lookup table
        $this->db->query('DROP TABLE IF EXISTS formas_pago');
    }

    public function down(): void
    {
        // Recreate lookup table with original data
        $this->db->query("
            CREATE TABLE formas_pago (
                id_form_pago       INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre_forma_pago  VARCHAR(50)  NOT NULL,
                tipo_pago          ENUM('EFECTIVO','TRANSFERENCIA','OTRO') NOT NULL,
                PRIMARY KEY (id_form_pago)
            ) ENGINE=InnoDB
        ");
        $this->db->query("INSERT INTO formas_pago (nombre_forma_pago, tipo_pago) VALUES ('Efectivo','EFECTIVO'),('Yape','OTRO'),('Plin','OTRO')");

        // Restore integer FK column
        $this->db->query('ALTER TABLE pagos ADD COLUMN id_form_pago INT UNSIGNED NULL DEFAULT NULL');

        // Map text back to IDs (best effort — custom "Otro" values become NULL)
        $this->db->query('
            UPDATE pagos p
            JOIN formas_pago fp ON fp.nombre_forma_pago = p.forma_pago
            SET p.id_form_pago = fp.id_form_pago
        ');

        $this->db->query('ALTER TABLE pagos ADD CONSTRAINT pagos_id_form_pago_foreign FOREIGN KEY (id_form_pago) REFERENCES formas_pago(id_form_pago) ON DELETE SET NULL ON UPDATE CASCADE');

        $this->db->query('ALTER TABLE pagos DROP COLUMN forma_pago');
    }
}
