<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Promociones extends Migration
{
    public function up()
    {
        // -- promociones --
        $this->forge->addField([
            'id_promocion' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre_promocion' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'fecha_inicio' => [
                'type' => 'DATE',
            ],
            'fecha_fin' => [
                'type' => 'DATE',
            ],
            'descuento' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'unsigned'   => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['ACTIVO', 'INACTIVO', 'VENCIDA'],
                'default'    => 'ACTIVO',
            ],
        ]);
        $this->forge->addKey('id_promocion', true);
        $this->forge->createTable('promociones');

        // -- promocion_paquete --
        $this->forge->addField([
            'id_promocion' => ['type' => 'INT', 'unsigned' => true],
            'id_paquete'   => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey(['id_promocion', 'id_paquete'], true);
        $this->forge->addForeignKey('id_promocion', 'promociones', 'id_promocion', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_paquete',   'paquetes',    'id_paquete',   'CASCADE', 'CASCADE');
        $this->forge->createTable('promociones_paquetes');

        // -- promocion_servicio --
        $this->forge->addField([
            'id_promocion' => ['type' => 'INT', 'unsigned' => true],
            'id_servicio'  => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey(['id_promocion', 'id_servicio'], true);
        $this->forge->addForeignKey('id_promocion', 'promociones', 'id_promocion', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_servicio',  'servicios',   'id_servicio',  'CASCADE', 'CASCADE');
        $this->forge->createTable('promociones_servicios');

        // -- promocion_producto --
        $this->forge->addField([
            'id_promocion' => ['type' => 'INT', 'unsigned' => true],
            'id_producto'  => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey(['id_promocion', 'id_producto'], true);
        $this->forge->addForeignKey('id_promocion', 'promociones', 'id_promocion', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_producto',  'productos',   'id_producto',  'CASCADE', 'CASCADE');
        $this->forge->createTable('promociones_productos');
    }

    public function down()
    {
        $this->forge->dropTable('promociones_productos');
        $this->forge->dropTable('promociones_servicios');
        $this->forge->dropTable('promociones_paquetes');
        $this->forge->dropTable('promociones');
    }
}
