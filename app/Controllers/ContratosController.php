<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ContratosController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view("Layouts/header"),
            'footer' => view("Layouts/footer"),
        ];

        return view("contratos/index", $data);
    }
}
