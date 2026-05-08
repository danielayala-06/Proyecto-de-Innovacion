<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class CotizacionController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('cotizaciones/index', $data);
    }

    public function crear()
    {
        $data = [
            'header'     => view('Layouts/header'),
            'footer'     => view('Layouts/footer'),
            'id_usuario' => 1, // TODO: reemplazar por id de sesión activa
        ];

        return view('cotizaciones/crear', $data);
    }

}
