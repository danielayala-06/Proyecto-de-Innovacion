<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Crea las tablas del sistema de reglas de bonificación:
 *
 *  reglas_paquetes — define la condición y el beneficio de cada regla.
 *  reglas_items    — junction table: asocia cada regla a N paquetes y/o productos.
 *
 * La relación regla ↔ paquete/producto es many-to-many: una regla puede aplicar
 * a varios paquetes o productos simultáneamente, y un paquete puede tener varias
 * reglas activas.
 */
class ReglasPaquetes extends Migration
{
    public function up()
    {
        // ── 1. Tabla de definición de reglas ─────────────────────────────────
        $this->forge->addField([
            'id_regla' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
            ],
            'tipo_condicion' => [
                'type'       => 'ENUM',
                'constraint' => ['CANTIDAD_MIN', 'CANTIDAD_MAX', 'ELEGIBILIDAD_MIN'],
            ],
            'valor_condicion' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'tipo_beneficio' => [
                'type'       => 'ENUM',
                'constraint' => ['producto_gratis', 'sesion_unica', 'otro'],
            ],
            'id_producto_beneficio' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
            ],
            'valor_beneficio' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
        ]);

        $this->forge->addKey('id_regla', true);
        $this->forge->addForeignKey(
            'id_producto_beneficio', 'productos', 'id_producto', 'SET NULL', 'CASCADE'
        );
        $this->forge->createTable('reglas_paquetes');

        // ── 2. Tabla intermedia regla ↔ paquete/producto ──────────────────────
        $this->forge->addField([
            'id_item' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_regla' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'tipo_item' => [
                'type'       => 'ENUM',
                'constraint' => ['paquete', 'producto'],
                'null'       => false,
            ],
            'id_referencia' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
        ]);

        $this->forge->addKey('id_item', true);
        $this->forge->addForeignKey('id_regla', 'reglas_paquetes', 'id_regla', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reglas_items');
    }

    public function down()
    {
        $this->forge->dropTable('reglas_items');
        $this->forge->dropTable('reglas_paquetes');
    }
}
