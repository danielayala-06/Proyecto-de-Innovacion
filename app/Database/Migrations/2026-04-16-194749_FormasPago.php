<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FormasPago extends Migration
{
    public function up()
    {
         $this->forge->addField([
            'id_form_pago'=>['type'=>'INT','auto_increment'=>true],
            'forma_pago'=>['type'=>'VARCHAR','constraint'=>50],
            'tipo_pago'=>['type'=>'VARCHAR','constraint'=>50],
        ]);
        $this->forge->addKey('id_form_pago', true);
        $this->forge->createTable('formas_pago');
    }

    public function down()
    {
        $this->forge->dropTable('formas_pago');
    }
}
