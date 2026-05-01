<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


/**
 *      RUTAS PARA COTIZACIONES
 */

$routes->get('/cotizaciones', 'CotizacionController::index');
$routes->get('/cotizaciones/(:num)', 'CotizacionController::index/$1');// Para paginaciones
$routes->get('/cotizaciones/crear', 'CotizacionController::create');
$routes->post('/cotizaciones/insertar', 'CotizacionController::createCotizacion');
$routes->post('/cotizaciones/searchCliente', 'CotizacionController::searchCliente');


/**
 *      RUTAS PARA CONTRATOS
 */
$routes->get('/contratos', 'ContratosController::index');

/**
 *      RUTAS PARA CLIENTES
 */
$routes->get('/clientes', 'ClientesController::index');

/**
 *      RUTAS PARA PAQUETES
 */
$routes->get('/paquetes', 'PaquetesController::index');

/**
 *      RUTAS PARA CALENDARIO
 */
$routes->get('/calendario', 'CalendarioController::index');







/**
 *      RUTAS PARA LAS API's
 */

//Cotizaciones
$routes->get('/api/cotizaciones','Api\Cotizaciones::getIndex');
$routes->get('/api/cotizaciones/(:num)','Api\Cotizaciones::getIndex/$1');
$routes->post('/api/cotizaciones','Api\Cotizaciones::postIndex');
$routes->put('/api/cotizaciones/(:num)','Api\Cotizaciones::putIndex/$1');
$routes->delete('/api/cotizaciones/(:num)','Api\Cotizaciones::deleteIndex/$1');

//Paquetes
$routes->get('/api/paquetes','Api\Paquetes::getIndex');
$routes->get('/api/paquetes/(:num)','Api\Paquetes::getIndex/$1');
$routes->post('/api/paquetes','Api\Paquetes::postIndex');
$routes->put('/api/paquetes/(:num)','Api\Paquetes::putIndex/$1');
$routes->delete('/api/paquetes/(:num)','Api\Paquetes::deleteIndex/$1');

//Clientes
$routes->get('/api/clientes','Api\Clientes::getIndex');
$routes->get('/api/clientes/(:num)','Api\Clientes::getIndex/$1');

//Productos
$routes->get('/api/productos', 'ProductosController::index');

//Servicios
$routes->get('/api/servicios', 'ServiciosController::index');