<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertPaqueteAnuarioSmall extends Migration
{
    public function up()
    {
        $descripcion = implode("\n", [
            'Portada foto laminada y biocuero. 20x20cm, 5 hojas, 10 páginas.',
            '8 fotos incluidas (toga para foto individual, no para foto grupal)',
            '2 con toga',
            '1 grupal',
            '1 con uniforme',
            '2 con ropa sport',
            '2 con familia',
            '1 sesión en el centro educativo (lunes a viernes)',
            '1 sesión en exterior o estudio',
            'Nota: +15 alumnos → cuadro laminado 40x30 para el docente',
            'Nota: +20 alumnos → anuario personalizado para el docente',
            'Nota: -12 alumnos → sesión en una sola fecha',
        ]);

        $this->db->table('paquetes')->insert([
            'nombre_paquete'   => 'Anuario Small',
            'nivel_disponible' => 'secundaria',
            'descripcion'      => $descripcion,
            'precio'           => 135.00,
            'estado'           => 'ACTIVO',
        ]);
    }

    public function down()
    {
        $this->db->table('paquetes')
            ->where('nombre_paquete', 'Anuario Small')
            ->delete();
    }
}
