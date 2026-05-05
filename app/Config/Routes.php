<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

/**
 *      RUTAS DE AUTENTICACIÓN
 */
$routes->get('/login',      'AuthController::index');
$routes->post('/login/auth', 'AuthController::login');
$routes->get('/logout',     'AuthController::logout');


/**
 *      RUTAS PARA COTIZACIONES
 */
$routes->get('/cotizaciones', 'CotizacionController::index');
$routes->get('/cotizaciones/(:num)', 'CotizacionController::index/$1');
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

//Productos
$routes->get('/api/productos',        'Api\Productos::getIndex');
$routes->get('/api/productos/(:num)', 'Api\Productos::getIndex/$1');

//Servicios
$routes->get('/api/servicios',        'Api\Servicios::getIndex');
$routes->get('/api/servicios/(:num)', 'Api\Servicios::getIndex/$1');

//Cotizaciones disponibles (debe ir ANTES de (:num) para no colisionar)
$routes->get('/api/cotizaciones/disponibles', 'Api\Cotizaciones::disponibles');

//Cotizaciones
$routes->get(   '/api/cotizaciones',                       'Api\Cotizaciones::getIndex');
$routes->get(   '/api/cotizaciones/(:num)',                'Api\Cotizaciones::getIndex/$1');
$routes->post(  '/api/cotizaciones',                       'Api\Cotizaciones::postIndex');
$routes->put(   '/api/cotizaciones/(:num)',                'Api\Cotizaciones::putIndex/$1');
$routes->delete('/api/cotizaciones/(:num)',                'Api\Cotizaciones::deleteIndex/$1');
$routes->patch( '/api/cotizaciones/(:num)/estado',         'Api\Cotizaciones::patchEstado/$1');

//Paquetes
$routes->get(   '/api/paquetes',          'Api\Paquetes::getIndex');
$routes->get(   '/api/paquetes/(:num)',   'Api\Paquetes::getIndex/$1');
$routes->post(  '/api/paquetes',          'Api\Paquetes::postIndex');
$routes->put(   '/api/paquetes/(:num)',   'Api\Paquetes::putIndex/$1');
$routes->delete('/api/paquetes/(:num)',   'Api\Paquetes::deleteIndex/$1');

//Clientes
$routes->get(  'api/clientes',        'Api\Clientes::getIndex');
$routes->get(  'api/clientes/dni',    'Api\Clientes::buscarPorDni');
$routes->post( 'api/clientes/dni',    'Api\Clientes::buscarPorDni');
$routes->get(  'api/clientes/(:num)', 'Api\Clientes::show/$1');

//Contratos
$routes->get(   '/api/contratos',        'Api\Contratos::getIndex');
$routes->get(   '/api/contratos/(:num)', 'Api\Contratos::getIndex/$1');
$routes->post(  '/api/contratos',        'Api\Contratos::postIndex');
$routes->put(   '/api/contratos/(:num)', 'Api\Contratos::putIndex/$1');
$routes->delete('/api/contratos/(:num)', 'Api\Contratos::deleteIndex/$1');

