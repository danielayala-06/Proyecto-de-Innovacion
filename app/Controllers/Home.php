<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer')
        ];
        return view('index', $data);
    }
}
