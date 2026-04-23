<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class CotizacionController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
        ];

        return view("cotizaciones/index", $data);
    }
    public function create()
    {
        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
        ];

        return view("cotizaciones/create", $data);
    }

    public function fetchCotizaciones()
    {

    }
}
