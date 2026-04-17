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

//Cotizaciones
$routes->get('/api/cotizaciones','Api\Cotizaciones::getIndex');
$routes->get('/api/cotizaciones/(:num)','Api\Cotizaciones::getIndex/$1');

//Paquetes
$routes->get('/api/paquetes','Api\Cotizaciones::getIndex');
$routes->get('/api/paquetes/(:num)','Api\Cotizaciones::getIndex/$1');
