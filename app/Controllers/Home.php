<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $data = [
            'header' => view('Partials/header'),
            'footer' => view('Partials/footer')
        ];
        return view('index', $data);
    }
}
