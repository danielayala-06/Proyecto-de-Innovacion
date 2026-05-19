<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Refactoriza la relación regla↔paquete/producto a many-to-many.
 *
 * Antes: reglas_paquetes tenía id_paquete (NOT NULL) e id_producto (NULL).
 *        → una regla podía ligarse a UN paquete o UN producto.
 * Ahora: tabla intermedia `reglas_items` permite N paquetes y N productos
 *        por regla.
 */
class CreateReglasItems extends Migration
{
    public function up()
    {
        // 1. Tabla intermedia ─────────────────────────────────────────────────
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

        // 2. Migrar datos existentes ──────────────────────────────────────────
        $reglas = $this->db->table('reglas_paquetes')->get()->getResultArray();

        foreach ($reglas as $r) {
            if (!empty($r['id_paquete'])) {
                $this->db->table('reglas_items')->insert([
                    'id_regla'      => $r['id_regla'],
                    'tipo_item'     => 'paquete',
                    'id_referencia' => $r['id_paquete'],
                ]);
            }
            if (!empty($r['id_producto'])) {
                $this->db->table('reglas_items')->insert([
                    'id_regla'      => $r['id_regla'],
                    'tipo_item'     => 'producto',
                    'id_referencia' => $r['id_producto'],
                ]);
            }
        }

        // 3. Quitar FK y columnas viejas de reglas_paquetes ───────────────────
        // Buscar los nombres reales de los FK constraints
        $fkRows = $this->db->query("
            SELECT CONSTRAINT_NAME, COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'reglas_paquetes'
              AND REFERENCED_TABLE_NAME IS NOT NULL
              AND COLUMN_NAME IN ('id_paquete', 'id_producto')
        ")->getResultArray();

        foreach ($fkRows as $row) {
            $this->db->query(
                "ALTER TABLE reglas_paquetes DROP FOREIGN KEY `{$row['CONSTRAINT_NAME']}`"
            );
        }

        // Eliminar las columnas ya redundantes
        $this->forge->dropColumn('reglas_paquetes', 'id_paquete');
        if ($this->db->fieldExists('id_producto', 'reglas_paquetes')) {
            $this->forge->dropColumn('reglas_paquetes', 'id_producto');
        }
    }

    public function down()
    {
        // Re-añadir columnas
        $this->forge->addColumn('reglas_paquetes', [
            'id_paquete' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'id_regla',
            ],
            'id_producto' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'id_paquete',
            ],
        ]);

        $this->forge->dropTable('reglas_items');
    }
}
