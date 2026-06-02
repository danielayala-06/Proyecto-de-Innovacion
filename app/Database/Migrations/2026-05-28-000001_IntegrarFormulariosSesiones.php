<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Enlaza el sistema de formularios (prom_*) con el sistema de sesiones:
 *
 *  prom_promociones.id_promocion_escolar → promociones_escolares.id_promocion
 *    Cuando se vincula, los formularios completados crean estudiantes automáticamente.
 *
 *  prom_alumnos.id_estudiante → estudiantes.id_estudiante
 *    Guarda el ID del estudiante creado al sincronizar, para evitar duplicados.
 */
class IntegrarFormulariosSesiones extends Migration
{
    public function up(): void
    {
        // Enlace de prom_promociones → promociones_escolares
        $this->forge->addColumn('prom_promociones', [
            'id_promocion_escolar' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'colegio_id',
            ],
        ]);

        // Registro del estudiante creado al sincronizar
        $this->forge->addColumn('prom_alumnos', [
            'id_estudiante' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'completado',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('prom_promociones', 'id_promocion_escolar');
        $this->forge->dropColumn('prom_alumnos',     'id_estudiante');
    }
}
