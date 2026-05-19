<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdProductoToReglasPaquetes extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('id_producto', 'reglas_paquetes')) {
            return;
        }

        $this->forge->addColumn('reglas_paquetes', [
            'id_producto' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'id_paquete',
            ],
        ]);

        $this->db->query(
            'ALTER TABLE reglas_paquetes
             ADD CONSTRAINT fk_reglas_id_producto
             FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down()
    {
        if (!$this->db->fieldExists('id_producto', 'reglas_paquetes')) {
            return;
        }
        $this->db->query('ALTER TABLE reglas_paquetes DROP FOREIGN KEY fk_reglas_id_producto');
        $this->forge->dropColumn('reglas_paquetes', 'id_producto');
    }
}
