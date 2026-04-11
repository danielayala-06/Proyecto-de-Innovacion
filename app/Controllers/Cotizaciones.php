<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Cotizacion;
use CodeIgniter\HTTP\ResponseInterface;
use DateInterval;

class Cotizaciones extends BaseController
{
    public function index()
    {
        $cotizaciones_list = (new Cotizacion())->findAll(10);
        $data= [
            'header' => view('Layouts/header'),
            'cotizaciones' => $cotizaciones_list,
            'footer' => view('Layouts/footer')
        ];

        return view('Cotizaciones/index', $data);
    }
}
