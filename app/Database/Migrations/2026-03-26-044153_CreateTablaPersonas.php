<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTablaPersonas extends Migration
{
    public function up()
    {
        /**
         * Building table PERSONAS
         */
        $this->forge->addField([
            'id_persona'          => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre_persona'       => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => false,
            ],
            'apellido_persona'       => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => false,
            ],
            'tipo_documento_persona'       => [
                'type'           => 'ENUM',
                'constraint'     => ['DNI', 'CARNET EXTRANJERIA', 'PASSPORT'],
                'default'        => 'DNI',
                'null'           => false,
            ],
            'numero_documento_persona'       => [
                'type'           => 'VARCHAR',
                'constraint'     => '20',
                'null'           => false,
            ],
            'telefono_persona'       => [
                'type'           => 'VARCHAR',
                'constraint'     => '20',
                'null'           => false,
            ],
            'telefono_opcional_persona'       => [
                'type'           => 'VARCHAR',
                'constraint'     => '20',
                'null'           => true,
            ],
            'email_persona'       => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => true,
            ]
        ]);

        // Constraints for the table Personas:
        $this->forge->addKey('id_persona', true);
        $this->forge->addUniqueKey('telefono_persona');
        $this->forge->addUniqueKey('correo_persona');
        $this->forge->createTable('personas');
    }

    /**
     * Comonly used for DROPPING the TABLE
     * @return void
     */
    public function down()
    {
        /**
         * Droping table PERSONAS ;-;
         */
        $this->forge->dropTable('personas');
    }
}
