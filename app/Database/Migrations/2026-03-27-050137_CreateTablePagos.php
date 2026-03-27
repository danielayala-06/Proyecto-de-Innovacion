<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTablePagos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pago' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_contrato' =>[
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'id_forma_pago' =>[
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'amortizacion' =>[
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'unsigned' => true,
                'null' => false,
            ],
            'saldo' =>[
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'unsigned' => true,
                'null' => false,
            ],
            'moneda' =>[
                'type' => 'ENUM',
                'constraint' => ['EUR', 'USD', 'PEN'],
                'default' => 'PEN',
                'null' => false,
            ]
        ]);
        //
        $this->forge->addField("fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()");

        $this->forge->addPrimaryKey('id_pago');
        $this->forge->addForeignKey('id_contrato','contratos','id_contrato');
        $this->forge->addForeignKey('id_forma_pago','formas_pago','id_forma_pago');

        $this->forge->createTable('pagos');
    }

    public function down()
    {
        $this->forge->dropTable('pagos');
    }
}
