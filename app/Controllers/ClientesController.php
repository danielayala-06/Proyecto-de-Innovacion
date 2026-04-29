<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Cliente;
use CodeIgniter\HTTP\ResponseInterface;

class ClientesController extends BaseController
{
    public function index()
    {
        $model    = new Cliente();
        $clientes = $model->clientesWithPersona();

        $data = [
            'header'   => view("Layouts/header"),
            'footer'   => view("Layouts/footer"),
            'clientes' => $clientes ?? [],
        ];

        return view("clientes/index", $data);
    }
}
