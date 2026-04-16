<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Cotizaciones extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view("layout/header"),
            'footer' => view("layout/footer"),
        ];

        return view("cotizaciones/index", $data);
    }

    public function fetchCotizaciones()
    {
        $data = [
            ['dni'=> '45256852', 'cliente'=>'FELIX TIPACTI, DIGGY T.', 'paquete'=> 'QUINO', 'created_at'=>date("Y-m-d H:i:s"), 'monto_estimado'=>2999.90, 'estado'=>'VIGENTE', 'fecha_evento'=>''  ],
            ['dni'=> '45256852', 'cliente'=>'FELIX TIPACTI, DIGGY T.', 'paquete'=> 'BODA', 'created_at'=>date("Y-m-d H:i:s"), 'monto_estimado'=>3000.90, 'estado'=>'VIGENTE', 'fecha_evento'=>'' ],
            ['dni'=> '57869235', 'cliente'=>'MIGUEL ALANYA, PACHAS', 'paquete'=> 'QUINO', 'created_at'=>date("Y-m-d H:i:s"), 'monto_estimado'=>5000.90, 'estado'=>'CADUCADO', 'fecha_evento'=>'' ],
            ['dni'=> '25356585', 'cliente'=>'EMILIANO, TIPACCTI', 'paquete'=> 'QUINO', 'created_at'=>date("Y-m-d H:i:s"), 'monto_estimado'=>1500.90, 'estado'=>'VIGENTE', 'fecha_evento'=>'' ],
            ['dni'=> '45256852', 'cliente'=>'FELIX TIPACTI, DIGGY T.', 'paquete'=> 'MATERNIDAD', 'created_at'=>date("Y-m-d H:i:s"), 'monto_estimado'=>9000.90, 'estado'=>'VIGENTE', 'fecha_evento'=>'' ],
            ['dni'=> '45715268', 'cliente'=>'MORGAN, MONDIOLI MOLINA', 'paquete'=> 'BODA', 'created_at'=>date("Y-m-d H:i:s"), 'monto_estimado'=>520.90, 'estado'=>'CADUCADO', 'fecha_evento'=>'' ]
        ];
    }
}
