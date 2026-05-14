<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaquetesSesiones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_paquete_sesion' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_paquete' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            // Tipo de lugar donde se realiza la sesión
            'tipo_sesion' => [
                'type'       => 'ENUM',
                'constraint' => ['exteriores', 'colegio', 'estudio', 'otro'],
                'default'    => 'colegio',
                'null'       => false,
            ],
            // Descripción opcional del lugar (ej: "Parque Kennedy", "Estudio principal")
            'lugar_descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            // Cuántas sesiones de este tipo incluye el paquete para una promoción
            'num_sesiones' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'default'  => 1,
                'null'     => false,
            ],
        ]);

        $this->forge->addKey('id_paquete_sesion', true);
        $this->forge->addForeignKey('id_paquete', 'paquetes', 'id_paquete', 'CASCADE', 'CASCADE');
        $this->forge->createTable('paquetes_sesiones');
    }

    public function down()
    {
        $this->forge->dropTable('paquetes_sesiones');
    }
}
