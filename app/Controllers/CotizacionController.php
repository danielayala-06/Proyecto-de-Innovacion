<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Cotizaciones\CotizacionService;

class CotizacionController extends BaseController
{
    protected CotizacionService $service;

    public function __construct()
    {
        $this->service = new CotizacionService();
    }

    public function index()
    {
        $cotizaciones = $this->service->listar([]);
        $resumenes    = $this->_calcularResumenes($cotizaciones);

        $data = [
            'header'       => view('Layouts/header'),
            'footer'       => view('Layouts/footer'),
            'cotizaciones' => $cotizaciones,
            'resumenes'    => $resumenes,
        ];

        return view('cotizaciones/index', $data);
    }

    public function crear()
    {
        $data = [
            'header'     => view('Layouts/header'),
            'footer'     => view('Layouts/footer'),
            'id_usuario' => 1, // TODO: reemplazar por id de sesión activa
        ];

        return view('cotizaciones/crear', $data);
    }

    private function _calcularResumenes(array $cotizaciones): array
    {
        $r = [
            'total'       => count($cotizaciones),
            'borrador'    => 0,
            'aprobadas'   => 0,
            'rechazadas'  => 0,
            'monto_total' => 0.0,
        ];

        foreach ($cotizaciones as $c) {
            $r['monto_total'] += (float) $c['total'];

            switch (strtoupper($c['estado'] ?? '')) {
                case 'BORRADOR':
                case 'PENDIENTE':
                    $r['borrador']++;
                    break;
                case 'APROBADA':
                    $r['aprobadas']++;
                    break;
                case 'RECHAZADA':
                    $r['rechazadas']++;
                    break;
            }
        }

        return $r;
    }
}
