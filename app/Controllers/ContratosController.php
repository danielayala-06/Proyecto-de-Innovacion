<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Contrato;
use App\Transformers\ContratosTransformer;

class ContratosController extends BaseController
{
    public function index()
    {
        $model       = new Contrato();
        $transformer = new ContratosTransformer();
        $contratos   = $transformer->transformMany($model->contratosConCliente());

        $data = [
            'header'   => view('Layouts/header'),
            'footer'   => view('Layouts/footer'),
            'contratos'=> $contratos,
        ];

        return view('contratos/index', $data);
    }
}
