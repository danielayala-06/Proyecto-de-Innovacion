<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class CotizacionController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view("Partials/header"),
            'footer' => view("Partials/footer"),
        ];
        return view('cotizaciones/index', $data);
    }
}
