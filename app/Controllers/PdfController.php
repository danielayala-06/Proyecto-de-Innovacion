<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PdfController extends BaseController
{
    public function generarContrato($cotizacion = null, $cliente = null)
    {
        $cotizacion = [
            "id_cotizacion" => "1",
            "id_cliente" => "1",
            "id_usuario" => "1",
            "nombre_cotizacion"=> "Boda Mendoza - Diciembre 2025",
            "num_dias_vigencia"=> "15",
            "fecha_registro"=> "2025-09-01",
            "fecha_hora_inicio"=> "2025-12-20 16:00:00",
            "fecha_hora_fin"=> "2025-12-21 02:00:00",
            "direccion"=> "Av. La Marina 2000, San Miguel",
            "referencia"=> "Frente al CC Plaza San Miguel",
            "latitud"=> "-12.077400",
            "longitud"=> "-77.090400",
            "observaciones"=> "Cliente solicita entrega del álbum antes de viajar.",
            "total_estimado"=> "2500.00",
            "estado"=> "APROBADA"
        ];
        $cliente = [
            "id_cliente"=> "1",
            "red_social"=> null,
            "id_persona"=> "10",
            "id_empresa"=> null,
            "metodo_comunicacion"=> "WHATSAPP",
            "estado"=> "ACTIVO",
            "persona"=> [
                "nombres"=> "ARELLA",
                "apellidos"=> "TAPIA HERNANDEZ",
                "telefono"=> "987678298",
                "correo"=> "tuarellitadediggy@gmail.com"
            ]
        ];
        $data = [
            'cotizacion'=>$cotizacion,
            'cliente'  => $cliente
        ];
        return view('pdf/contratos/contrato', $data);
    }
}
