<?php

/**
 * @file    ClienteController.php
 * @package App\Controllers
 *
 * Controlador web para el módulo de Clientes.
 * Renderiza la vista de gestión de clientes; toda la lógica CRUD es
 * manejada por el módulo JS (clientes) que consume la API REST.
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Sirve la vista principal del módulo de clientes.
 *
 * Ruta:
 *  - GET /clientes → index()
 */
class ClienteController extends BaseController
{
    /**
     * Renderiza la vista de gestión de clientes con el layout estándar.
     *
     * El listado, creación y edición de clientes se realizan desde el frontend
     * a través de llamadas a la API; este controlador solo inyecta el layout.
     *
     * @return string HTML de la vista renderizada.
     */
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('clientes/index', $data);
    }
}
