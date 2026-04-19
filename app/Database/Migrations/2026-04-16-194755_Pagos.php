<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Pagos extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_pago'=>[
                'type'=>'INT',
                'auto_increment'=>true,
                'unsigned'=>true
            ],
            'fecha'=>[
                'type'=>'DATE',
                'default'=>date('Y-m-d')
            ],
            'monto'=>[
                'type'=>'DECIMAL',
                'constraint'=>'10,2',
                'unsigned'=>true
            ],
            'moneda'=>[
                'type'=>'ENUM',
                'constraint'=>['PEN', 'EUR', 'USD'],
                'default'=>'PEN'
            ],
            'voucher'=>[
                'type'=>'VARCHAR',
                'constraint'=>255,
                'null'=>true
            ],
            'id_form_pago'=>[
                'type'=>'INT',
                'unsigned'=>true
            ],
            'id_contrato'=>[
                'type'=>'INT',
                'unsigned'=>true
            ],
             'estado'=>[
                 'type'=>'ENUM',
                 'constraint'=>['PENDIENTE', 'COMPLETADO', 'ANULADO'],
                 'default'=>'PENDIENTE'
             ]
        ]);
        $this->forge->addKey('id_pago', true);
        $this->forge->addKey('id_contrato');
        //$this->forge->addKey('estado');

        $this->forge->addForeignKey('id_form_pago','formas_pago','id_form_pago','RESTRICT','RESTRICT');
        $this->forge->addForeignKey('id_contrato','contratos','id_contrato','RESTRICT','RESTRICT');
        $this->forge->createTable('pagos');
    }

    public function down()
    {
        $this->forge->dropTable('pagos');
    }
}
