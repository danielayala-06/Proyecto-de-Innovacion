<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaquetesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('paquetes')->insertBatch([
            [
                'nombre_paquete'    =>'Paquete Básico',
                'precio_base'       =>500.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye fotografía',
                'estado'            =>'activo'
            ],
            [
                'nombre_paquete'    =>'Paquete Premium',
                'precio_base'       =>1200.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Foto + video + álbum',
                'estado'            =>'activo'
            ],
            [
                'nombre_paquete'    =>'ANUARIO SMALL',
                'precio_base'       =>135.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye 1 sesion de fotos en el centro educativo, en el horario de lunes a viernes y 1 sesion en exterior o estudio',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'ANUARIO MEDIUM',
                'precio_base'       =>145.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye 1 sesion de fotos en el centro educativo, en el horario de lunes a viernes y 1 sesion en exterior o estudio',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'ANUARIO BIG',
                'precio_base'       =>150.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye 1 sesion de fotos en el centro educativo, en el horario de lunes a viernes y 1 sesion en exterior o estudio',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'ANUARIO BIG PREMIUM',
                'precio_base'       =>210.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye 3 sesiones: 
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exteriores.
                1 sesion en estudio',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'BLANCO PREMIUM BRILLANTE',
                'precio_base'       =>150.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye 2 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exterior o estudio.',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'ACADEMICO',
                'precio_base'       =>145.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye 2 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exterior o estudio.',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'MARAVILLAS DEL MUNDO',
                'precio_base'       =>110.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye 2 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exterior o estudio.',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'MARAVILLAS DEL MUNDO BASIC',
                'precio_base'       =>110.00,
                'categoria'         =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'       =>'Incluye 1 sesion de fotos.',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'     =>'CUADROS BRILLANTES',
                'precio_base'        =>150.00,
                'categoria'          =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'        =>'Incluye 2 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exterior o estudio.',
                'estado'             =>'ACTIVO'
            ],
            [
                'nombre_paquete'     =>'CUADROS ENCAJADOS',
                'precio_base'        =>160.00,
                'categoria'          =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'        =>'Incluye 2 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exterior o estudio.',
                'estado'             =>'ACTIVO'
            ],
            [
                'nombre_paquete'     =>'PACK ADIOS PRIMARIA',
                'precio_base'        =>240.00,
                'categoria'          =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'        =>'Incluye 2 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exterior o estudio.',
                'estado'             =>'ACTIVO'
            ],
            [
                'nombre_paquete'     =>'PACK MIS RECUERDOS',
                'precio_base'        =>310.00,
                'categoria'          =>'ESCOLAR',
                'imagen'             =>null,
                'descripcion'        =>'Incluye 2 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exterior o estudio.',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'PACK PREMIUM',
                'precio_base'       =>350.00,
                'categoria'         =>'ESCOLAR',
                'imagen'            =>null,
                'descripcion'       =>'Incluye 2 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exterior o estudio.',
                'estado'            =>'ACTIVO'
            ],
            [
                'nombre_paquete'    =>'PACK PREMIUM GOLD',
                'precio_base'       =>420.00,
                'categoria'         =>'ESCOLAR',
                'imagen'            =>null,
                'descripcion'       =>'Incluye 3 sesiones:
                1 sesion de fotos en el centro educativo, en el horario de lunes a viernes.
                1 sesion en exteriores. 
                1 sesion en estudio.',
                'estado'            =>'ACTIVO'
            ],
            [
             'nombre_paquete' => 'Paquete Básico',
             'categoria'      => 'SESION',
             'imagen'         => null,
             'descripcion'    => 'Ideal para sesiones pequeñas. Incluye fotografía de sesión y fotografías digitales.',
             'precio_base'    => 350.00,
             'estado'         => 'ACTIVO',
         ],
            [
             'nombre_paquete' => 'Paquete Bodas Gold',
             'categoria'      => 'EVENTO',
             'imagen'         => null,
             'descripcion'    => 'Cobertura completa de bodas con foto, video y álbum premium incluido.',
             'precio_base'    => 2500.00,
             'estado'         => 'ACTIVO',
         ],
            [
             'nombre_paquete' => 'Paquete Anuario Escolar',
             'categoria'      => 'ESCOLAR',
             'imagen'         => null,
             'descripcion'    => 'Sesión fotográfica grupal e individual + anuario impreso para cada alumno.',
             'precio_base'    => 75.00,
             'estado'         => 'ACTIVO',
         ],
            [
             'nombre_paquete' => 'Paquete Marketing Digital',
             'categoria'      => 'SESION',
             'imagen'         => null,
             'descripcion'    => 'Fotografía de evento + cobertura y publicación en redes sociales.',
             'precio_base'    => 800.00,
             'estado'         => 'ACTIVO',
         ],

        ]);
    }
}
