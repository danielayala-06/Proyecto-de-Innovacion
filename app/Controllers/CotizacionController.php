<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Cotizacion;
use CodeIgniter\HTTP\ResponseInterface;

class CotizacionController extends BaseController
{
    public function index(int $page = 1)
    {
        $modelCotizacion = new Cotizacion();// Accedemos al model
        $cotizaciones = $modelCotizacion->getCotizacionesResumen(10, $page);
        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
            'cotizaciones' => $cotizaciones,
        ];

        return view("cotizaciones/index", $data);
    }
    public function create()
    {
        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
        ];

        return view("cotizaciones/crear", $data);
    }

    public function fetchCotizaciones()
    {

    }
}
