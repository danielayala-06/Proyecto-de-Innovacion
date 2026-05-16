<?php

/**
 * @file    CalendarioController.php
 * @package App\Controllers
 *
 * Controlador web para el módulo de Calendario de sesiones fotográficas.
 * Renderiza la vista del calendario; toda la lógica de datos es manejada
 * por el módulo JS (sesiones) que consume la API.
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Sirve la vista principal del calendario de sesiones fotográficas.
 *
 * Ruta:
 *  - GET /calendario → index()
 */
class CalendarioController extends BaseController
{
    /**
     * Renderiza la vista del calendario con el layout estándar.
     *
     * El calendario es manejado íntegramente en el frontend; este controlador
     * solo inyecta el layout (header/footer) en la vista.
     *
     * @return string HTML de la vista renderizada.
     */
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('calendario/index', $data);
    }
}
