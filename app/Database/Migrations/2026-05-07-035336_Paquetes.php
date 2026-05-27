<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Paquetes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_paquete'=>
                ['type'=>'INT','auto_increment'=>true,'unsigned'=>true,
                ],
            'nombre_paquete'=>
                ['type'=>'VARCHAR','constraint'=>150
                ],
            'nivel_disponible'=>
                ['type'=>'ENUM','constraint'=>['inicial', 'primaria', 'secundaria', 'postgrado', 'otro'],'default'=>'otro','null'=>false,],
            'descripcion'=>
                ['type'=>'TEXT','null'=>true
                ],
            'categoria' => [
                'type'       => 'ENUM',
                'constraint' => ['Cuadros', 'Anuarios', 'Paquetes', 'otros'],
                'null'       => true,
                'default'    => null,
                'after'      => 'nivel_disponible',
            ],
            'imagen'=>
                ['type'=>'VARCHAR','constraint'=>255,'null'=>true
                ],
            'precio'=>
                ['type'=>'DECIMAL','constraint'=>'7,2','unsigned'=>true,'null'=>false
                ],
            'estado'=>
                ['type'=>'ENUM','constraint'=>['ACTIVO','INACTIVO'],'default'=>'ACTIVO'
                ]
        ]);
        $this->forge->addKey('id_paquete', true);
        $this->forge->createTable('paquetes');
    }

    public function down()
    {
        $this->forge->dropTable('paquetes');
    }
}
