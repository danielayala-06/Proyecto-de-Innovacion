<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PromAlumnosSeeder extends Seeder
{
    public function run()
    {
        // Obtener el id de la primera promoción creada
        $promocion = $this->db->table('prom_promociones')->orderBy('id', 'ASC')->limit(1)->get()->getRowArray();

        if (!$promocion) {
            echo "  [PromAlumnosSeeder] No hay promociones. Ejecuta PromPromocionesSeeder primero.\n";
            return;
        }

        $nombres = [
            'García Pérez, Juan Carlos',
            'López Torres, María Fernanda',
            'Mendoza Lima, Carlos Alberto',
        ];

        foreach ($nombres as $nombre) {
            $this->db->table('prom_alumnos')->insert([
                'promocion_id' => $promocion['id'],
                'nombre'       => $nombre,
                'token'        => bin2hex(random_bytes(32)),
                'completado'   => 0,
                'enviado'      => 0,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
