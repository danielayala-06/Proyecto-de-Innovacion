<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PaquetesController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view("Layouts/header"),
            'footer' => view("Layouts/footer"),
        ];

        return view("paquetes/index", $data);
    }
}
