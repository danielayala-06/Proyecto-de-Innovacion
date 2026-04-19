<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CotizacionesSeeder extends Seeder
{
    public function run()
    {
         $this->db->table('cotizaciones')->insertBatch([
             [
                 'id_cliente'        => 1,
                 'id_usuario'        => 1, // Admin
                 'nombre_cotizacion' => 'Boda Mendoza - Diciembre 2025',
                 'num_dias_vigencia' => 15,
                 'fecha_registro'    => '2025-09-01',
                 'fecha_hora_inicio' => '2025-12-20 16:00:00',
                 'fecha_hora_fin'    => '2025-12-21 02:00:00',
                 'direccion'         => 'Av. La Marina 2000, San Miguel',
                 'referencia'        => 'Frente al CC Plaza San Miguel',
                 'latitud'           => -12.0774000,
                 'longitud'          => -77.0904000,
                 'observaciones'     => 'Cliente solicita entrega del álbum antes de viajar.',
                 'total_estimado'    => 2500.00,
                 'estado'            => 'CONVERTIDA',
             ],
             [
                 'id_cliente'        => 2,
                 'id_usuario'        => 1,
                 'nombre_cotizacion' => 'Anuario Escolar Colegio Los Pinos 2025',
                 'num_dias_vigencia' => 30,
                 'fecha_registro'    => '2025-08-15',
                 'fecha_hora_inicio' => '2025-10-10 08:00:00',
                 'fecha_hora_fin'    => '2025-10-10 17:00:00',
                 'direccion'         => 'Jr. Los Álamos 345, Surco',
                 'referencia'        => 'Ingreso por puerta lateral del colegio',
                 'latitud'           => -12.1500000,
                 'longitud'          => -76.9900000,
                 'observaciones'     => '200 alumnos en total distribuidos en 8 salones.',
                 'total_estimado'    => 15000.00,
                 'estado'            => 'APROBADA',
             ],
             [
                 'id_cliente'        => 3,
                 'id_usuario'        => 1,
                 'nombre_cotizacion' => 'Evento Corporativo Lima Norte',
                 'num_dias_vigencia' => 7,
                 'fecha_registro'    => '2025-09-10',
                 'fecha_hora_inicio' => '2025-11-05 09:00:00',
                 'fecha_hora_fin'    => '2025-11-05 18:00:00',
                 'direccion'         => 'Av. Túpac Amaru 1800, Independencia',
                 'referencia'        => null,
                 'latitud'           => -11.9900000,
                 'longitud'          => -77.0500000,
                 'observaciones'     => 'Requiere cobertura en redes sociales en tiempo real.',
                 'total_estimado'    => 800.00,
                 'estado'            => 'ENVIADA',
             ],
             [
                 'id_cliente'        => 4,
                 'id_usuario'        => 1,
                 'nombre_cotizacion' => 'Sesión Fotos Mariana - Familia',
                 'num_dias_vigencia' => 10,
                 'fecha_registro'    => '2025-09-20',
                 'fecha_hora_inicio' => '2025-10-25 10:00:00',
                 'fecha_hora_fin'    => '2025-10-25 12:00:00',
                 'direccion'         => 'Parque Kennedy, Miraflores',
                 'referencia'        => 'Zona de las gatas',
                 'latitud'           => -12.1210000,
                 'longitud'          => -77.0300000,
                 'observaciones'     => null,
                 'total_estimado'    => 350.00,
                 'estado'            => 'BORRADOR',
             ],
        ]);

         // cot_paqutes:
        $this->db->table('cotizaciones_paquetes')->insertBatch([
            [
                'id_cotizacion'   => 1,
                'id_paquete'      => 2,   // Bodas Gold
                'cantidad'        => 1,
                'precio_unitario' => 2500.00,
                'subtotal'        => 2500.00,
            ],
            [
                'id_cotizacion'   => 2,
                'id_paquete'      => 3,   // Anuario Escolar
                'cantidad'        => 200,
                'precio_unitario' => 75.00,
                'subtotal'        => 15000.00,
            ],
            [
                'id_cotizacion'   => 3,
                'id_paquete'      => 4,   // Marketing Digital
                'cantidad'        => 1,
                'precio_unitario' => 800.00,
                'subtotal'        => 800.00,
            ],
            [
                'id_cotizacion'   => 4,
                'id_paquete'      => 1,   // Básico
                'cantidad'        => 1,
                'precio_unitario' => 350.00,
                'subtotal'        => 350.00,
            ],
        ]);

        // cot_servicios: tabla grande → 2 registros
        $this->db->table('cotizaciones_servicios')->insertBatch([
            [
                'id_cotizacion'   => 1,
                'id_servicio'     => 2,   // Video cinematográfico adicional
                'cantidad'        => 1,
                'precio_unitario' => 0.00, // incluido en el paquete
                'subtotal'        => 0.00,
            ],
            [
                'id_cotizacion'   => 3,
                'id_servicio'     => 4,   // Marketing RRSS adicional
                'cantidad'        => 1,
                'precio_unitario' => 0.00,
                'subtotal'        => 0.00,
            ],
        ]);

        // cot_productos: tabla grande → 2 registros
        $this->db->table('cotizaciones_productos')->insertBatch([
            [
                'id_cotizacion'   => 1,
                'id_producto'     => 1,   // Álbum premium
                'cantidad'        => 1,
                'precio_unitario' => 0.00, // incluido en paquete Bodas Gold
                'subtotal'        => 0.00,
            ],
            [
                'id_cotizacion'   => 2,
                'id_producto'     => 4,   // Fotolibro mediano (regla: 200 >= 15)
                'cantidad'        => 8,   // 1 por salón
                'precio_unitario' => 0.00, // gratis por regla de paquete
                'subtotal'        => 0.00,
            ],
        ]);
    }
}
