<?php

namespace App\Controllers;

class PromocionController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('promociones/index', $data);
    }
}
