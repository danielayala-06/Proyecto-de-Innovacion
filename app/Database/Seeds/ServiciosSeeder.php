<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('servicios')->insertBatch([
            ['nombre_servicio'=>'FOTOGRAFIA','detalle_servicio'=>'COBERTURA','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'SESION FOTOGRAFICA MATERNIDAD VIP','detalle_servicio'=>'12 fotos digitales con un cuadro laminado de 12x20','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'SESION FOTOGRAFICA PAREJAS PREMIUN','detalle_servicio'=>'25 fotos digitales con un libro virtual','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'FOTOGRAFIA','detalle_servicio'=>'COBERTURA','estado'=>'ACTIVO'],

            // Sesión
            ['nombre_servicio'=>'SESION FOTOGRAFICA PRE PARTY','detalle_servicio'=>'Sesion en estudio o exteriores con cuadro de firma','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'SESION FOTOGRAFICA EVENTO','detalle_servicio'=>'Cobertura de fiesta por horas','estado'=>'ACTIVO'],

            // Fotos digitales
            ['nombre_servicio'=>'FOTOS DIGITALES','detalle_servicio'=>'Entrega de fotos digitales en alta calidad','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'FOTOS DIGITALES USB','detalle_servicio'=>'Entrega de fotos en dispositivo USB','estado'=>'ACTIVO'],

            // Impresiones
            ['nombre_servicio'=>'FOTOLIBRO','detalle_servicio'=>'Photobook 25x20 con varias hojas','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'FOTOS IMPRESAS 10x15','detalle_servicio'=>'Impresiones en tamaño 10x15','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'AMPLIACION 20x30','detalle_servicio'=>'Foto ampliada tamaño 20x30','estado'=>'ACTIVO'],

            // Video
            ['nombre_servicio'=>'VIDEO CLIP PRE PARTY','detalle_servicio'=>'Video clip de 3 minutos','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'VIDEO COBERTURA EVENTO','detalle_servicio'=>'Grabacion de evento por horas','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'VIDEO RESUMEN','detalle_servicio'=>'Video editado resumen de la fiesta','estado'=>'ACTIVO'],

            // Extras
            ['nombre_servicio'=>'DRONE','detalle_servicio'=>'Servicio de tomas aereas con drone','estado'=>'ACTIVO'],
            ['nombre_servicio'=>'CAMARA ADICIONAL','detalle_servicio'=>'Camara extra para cobertura del evento','estado'=>'ACTIVO'],
        ]);
    }
}
