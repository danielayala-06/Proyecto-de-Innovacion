<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeIdFormPagoNullable extends Migration
{
    public function up(): void
    {
        // Drop FK before altering the column
        $this->db->query('ALTER TABLE pagos DROP FOREIGN KEY pagos_id_form_pago_foreign');
        $this->db->query('ALTER TABLE pagos MODIFY COLUMN id_form_pago INT UNSIGNED NULL DEFAULT NULL');
        $this->db->query('ALTER TABLE pagos ADD CONSTRAINT pagos_id_form_pago_foreign FOREIGN KEY (id_form_pago) REFERENCES formas_pago(id_form_pago) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE pagos DROP FOREIGN KEY pagos_id_form_pago_foreign');
        $this->db->query('UPDATE pagos SET id_form_pago = 1 WHERE id_form_pago IS NULL');
        $this->db->query('ALTER TABLE pagos MODIFY COLUMN id_form_pago INT UNSIGNED NOT NULL');
        $this->db->query('ALTER TABLE pagos ADD CONSTRAINT pagos_id_form_pago_foreign FOREIGN KEY (id_form_pago) REFERENCES formas_pago(id_form_pago) ON DELETE RESTRICT ON UPDATE RESTRICT');
    }
}
