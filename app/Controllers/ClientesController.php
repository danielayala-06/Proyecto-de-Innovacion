<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ClientesController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view("Layouts/header"),
            'footer' => view("Layouts/footer"),
        ];

        return view("clientes/index", $data);
    }
}
