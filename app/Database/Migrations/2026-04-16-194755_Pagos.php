<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Pagos extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_pago'=>['type'=>'INT','auto_increment'=>true],
            'fecha'=>['type'=>'DATE'],
            'monto'=>['type'=>'DECIMAL','constraint'=>'10,2'],
            'moneda'=>['type'=>'VARCHAR','constraint'=>10],
            'voucher'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'id_form_pago'=>['type'=>'INT'],
            'id_contrato'=>['type'=>'INT'],
        ]);
        $this->forge->addKey('id_pago', true);
        $this->forge->addForeignKey('id_form_pago','formas_pago','id_form_pago','CASCADE','CASCADE');
        $this->forge->addForeignKey('id_contrato','contratos','id_contrato','CASCADE','CASCADE');
        $this->forge->createTable('pagos');
    }

    public function down()
    {
        $this->forge->dropTable('pagos');
    }
}
