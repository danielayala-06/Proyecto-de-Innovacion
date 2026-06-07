<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCliente2ToCotizaciones extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cotizaciones', [
            'id_cliente2' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'id_cliente',
            ],
        ]);

        $this->db->query(
            'ALTER TABLE cotizaciones
             ADD CONSTRAINT fk_cotizaciones_cliente2
             FOREIGN KEY (id_cliente2) REFERENCES clientes(id_cliente)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE cotizaciones DROP FOREIGN KEY fk_cotizaciones_cliente2');
        $this->forge->dropColumn('cotizaciones', 'id_cliente2');
    }
}
