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
$routes->post('/api/cotizaciones','Api\Cotizaciones::postIndex');

//Paquetes
$routes->get('/api/paquetes','Api\Paquetes::getIndex');
$routes->get('/api/paquetes/(:num)','Api\Paquetes::getIndex/$1');

//Clientes
$routes->get('api/clientes','Api\Clientes::getIndex');
$routes->get('api/clientes/(:num)','Api\Clientes::getIndex/$1');