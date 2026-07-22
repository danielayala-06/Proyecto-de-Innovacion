<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Crea la tabla `estudiante_productos` para rastrear qué productos
 * del paquete contratado han sido asignados a cada estudiante.
 *
 * Diseño genérico: un registro por (estudiante, producto), sin depender
 * de categorías fijas como "cuadro" o "anuario".
 */
class AddProductosToEstudiantes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_estudiante' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'id_producto' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
        ]);

        $this->forge->addPrimaryKey(['id_estudiante', 'id_producto']);
        $this->forge->addForeignKey('id_estudiante', 'estudiantes', 'id_estudiante', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_producto',   'productos',   'id_producto',   'CASCADE', 'CASCADE');

        $this->forge->createTable('estudiante_productos', true);
    }

    public function down()
    {
        $this->forge->dropTable('estudiante_productos', true);
    }
}
