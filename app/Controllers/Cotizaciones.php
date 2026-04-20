<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Cotizaciones extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view("layout/header"),
            'footer' => view("layout/footer"),
        ];

        return view("cotizaciones/index", $data);
    }

    public function fetchCotizaciones()
    {

    }
}
