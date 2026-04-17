<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


/**
 *      RUTAS PARA COTIZACIONES
 */

$routes->get('/cotizaciones/crear', 'CotizacionController::index');

/**
 *      RUTAS PARA LAS API's
 */
$routes->get('/api/cotizaciones','Api\Cotizaciones::getIndex');