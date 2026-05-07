<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RolesPermisos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_rol' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'id_permiso' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
        ]);

        $this->forge->addKey(['id_rol', 'id_permiso'], true);
        $this->forge->addForeignKey('id_rol',     'roles',    'id_rol',     'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_permiso', 'permisos', 'id_permiso', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rol_permisos');
    }

    public function down()
    {
        $this->forge->dropTable('rol_permisos');
    }
}
