<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ContratosModel;
use CodeIgniter\HTTP\ResponseInterface;

class ContratoController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('contratos/index', $data);
    }

    public function generarPDF(int $id)
    {
        $model = new ContratosModel();

        $contrato = $model->find($id) ?? null;

        if(!$contrato)return view('errors/html/error_404');

        return view('pdf/contrato');
    }
}
