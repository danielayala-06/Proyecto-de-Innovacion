<?php

/**
 * @file    PaqueteController.php
 * @package App\Controllers
 *
 * Controlador web para el módulo de Paquetes fotográficos.
 * Renderiza la vista de gestión de paquetes; toda la lógica CRUD es
 * manejada por el módulo JS (paquetes) que consume la API REST.
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Sirve la vista principal del módulo de paquetes fotográficos.
 *
 * Ruta:
 *  - GET /paquetes → index()
 */
class PaqueteController extends BaseController
{
    /**
     * Renderiza la vista de gestión de paquetes con el layout estándar.
     *
     * El listado, creación, edición y cambio de estado de paquetes se
     * realizan desde el frontend a través de llamadas a la API.
     *
     * @return string HTML de la vista renderizada.
     */
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('paquetes/index', $data);
    }
}
