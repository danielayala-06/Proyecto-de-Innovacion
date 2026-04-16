<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Empresas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_empresa' => ['type'=>'INT','auto_increment'=>true],
            'razon_social' => ['type'=>'VARCHAR','constraint'=>150],
            'ruc' => ['type'=>'VARCHAR','constraint'=>20],
            'nombre_comercial' => ['type'=>'VARCHAR','constraint'=>150],
            'telefono' => ['type'=>'VARCHAR','constraint'=>20],
            'correo' => ['type'=>'VARCHAR','constraint'=>150],
        ]);
        $this->forge->addKey('id_empresa', true);
        $this->forge->createTable('empresas');
    }

    public function down()
    {
        $this->forge->dropTable('empresas');
    }
}
