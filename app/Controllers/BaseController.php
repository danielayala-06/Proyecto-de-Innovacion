<?php

/**
 * @file    BaseController.php
 * @package App\Controllers
 *
 * Controlador base del que heredan todos los controladores web de la aplicación.
 * Provee el punto de inicialización centralizado para helpers, servicios y
 * cualquier dependencia compartida entre controladores.
 *
 * Para agregar helpers globales, descomenta la línea:
 *   $this->helpers = ['form', 'url'];
 *
 * Para cargar la sesión en todas las vistas, descomenta:
 *   $this->session = service('session');
 */

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Controlador abstracto base de la aplicación.
 *
 * Todos los controladores web heredan de esta clase.
 * Por seguridad, cualquier método nuevo en subclases debe ser
 * declarado como `protected` o `private` si no es una acción de ruta.
 */
abstract class BaseController extends Controller
{
    /**
     * Instancia de sesión compartida (descomentar si se necesita en todas las vistas).
     *
     * @var \CodeIgniter\Session\Session|null
     */
    // protected $session;

    /**
     * Inicializa el controlador base con las dependencias de CI4.
     *
     * Se ejecuta antes de cada acción del controlador. Es el lugar
     * correcto para cargar helpers globales, inicializar la sesión
     * o pre-cargar modelos/librerías comunes.
     *
     * @param  RequestInterface  $request  Objeto de petición HTTP.
     * @param  ResponseInterface $response Objeto de respuesta HTTP.
     * @param  LoggerInterface   $logger   Logger PSR-3.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Cargar helpers disponibles en todos los controladores hijos:
        // $this->helpers = ['form', 'url'];

        parent::initController($request, $response, $logger);

        // Pre-cargar modelos, librerías, etc.:
        // $this->session = service('session');
    }
}
