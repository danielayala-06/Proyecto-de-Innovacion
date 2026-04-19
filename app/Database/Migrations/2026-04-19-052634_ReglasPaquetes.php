<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ReglasPaquetes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_regla' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_paquete' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
            ],
            'tipo_condicion' => [
                'type'       => 'ENUM',
                'constraint' => ['CANTIDAD_MIN', 'CANTIDAD_MAX'],
            ],
            'valor_condicion' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'tipo_beneficio' => [
                'type'       => 'ENUM',
                'constraint' => ['PRODUCTO_GRATIS', 'DESCUENTO_PORCENTAJE', 'DESCUENTO_FIJO', 'SESION_UNICA'],
            ],
            'valor_beneficio' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
        ]);

        $this->forge->addKey('id_regla', true);
        $this->forge->addForeignKey('id_paquete', 'paquetes', 'id_paquete', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reglas_paquetes');
    }

    public function down()
    {
        $this->forge->dropTable('reglas_paquetes');
    }
}
