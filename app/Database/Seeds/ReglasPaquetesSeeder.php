<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReglasPaquetesSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('reglas_paquetes')->insertBatch([
            [
                'id_paquete'      => 3, // Paquete Anuario Escolar
                'descripcion'     => 'Si se contratan 15 o más paquetes, se agrega un fotolibro mediano gratis por salón.',
                'tipo_condicion'  => 'CANTIDAD_MIN',
                'valor_condicion' => 15.00,
                'tipo_beneficio'  => 'PRODUCTO_GRATIS',
                'valor_beneficio' => '4', // id_producto = 4 (Fotolibro mediano)
            ],
            [
                'id_paquete'      => 3, // Paquete Anuario Escolar
                'descripcion'     => 'Si se contratan menos de 12 paquetes, la sesión se realiza en una sola fecha.',
                'tipo_condicion'  => 'CANTIDAD_MAX',
                'valor_condicion' => 11.00,
                'tipo_beneficio'  => 'SESION_UNICA',
                'valor_beneficio' => 'La sesión fotográfica se realizará en una única fecha pactada.',
            ],
            [
                'id_paquete'      => 2, // Paquete Bodas Gold
                'descripcion'     => 'Descuento del 10% si se contrata con más de 60 días de anticipación.',
                'tipo_condicion'  => 'CANTIDAD_MIN',
                'valor_condicion' => 1.00,
                'tipo_beneficio'  => 'DESCUENTO_PORCENTAJE',
                'valor_beneficio' => '10',
            ],
            [
                'id_paquete'      => 4, // Paquete Marketing Digital
                'descripcion'     => 'Descuento fijo de S/. 50 al contratar 2 o más paquetes.',
                'tipo_condicion'  => 'CANTIDAD_MIN',
                'valor_condicion' => 2.00,
                'tipo_beneficio'  => 'DESCUENTO_FIJO',
                'valor_beneficio' => '50',
            ],
        ]);
    }
}
