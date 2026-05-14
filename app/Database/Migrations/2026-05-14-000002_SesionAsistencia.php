<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SesionAsistencia extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_asistencia' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_sesion' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'id_estudiante' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            // null = no marcado aún, 1 = asistió, 0 = ausente
            'asistio' => [
                'type'    => 'TINYINT',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id_asistencia', true);
        $this->forge->addUniqueKey(['id_sesion', 'id_estudiante']);
        $this->forge->addForeignKey('id_sesion',     'sesiones_fotograficas', 'id_sesion',     'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_estudiante',  'estudiantes',           'id_estudiante', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sesion_asistencia');
    }

    public function down()
    {
        $this->forge->dropTable('sesion_asistencia');
    }
}
