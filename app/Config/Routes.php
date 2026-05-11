<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

// ============================================================================
// Sección VISTAS de la App
// ============================================================================

// COTIZACIONES
$routes->get('/cotizaciones',       'CotizacionController::index');
$routes->get('/cotizaciones/crear', 'CotizacionController::crear');

// CONTRATOS
$routes->get('/contratos',       'ContratoController::index');
$routes->get('/contratos/(:num)','ContratoController::generarContrato/$1');
//$routes->get('/cotizaciones/crear', 'CotizacionController::crear');

// PAQUETES
$routes->get('/paquetes',       'PaqueteController::index');
//$routes->get('/cotizaciones/crear', 'CotizacionController::crear');

// CALENDARIO
$routes->get('/calendario',       'CalendarioController::index');
//$routes->get('/cotizaciones/crear', 'CotizacionController::crear');

// CLIENTES
$routes->get('/clientes',       'ClienteController::index');
//$routes->get('/cotizaciones/crear', 'CotizacionController::crear');

// CALENDARIO
$routes->get('/calendario',       'CalendarioController::index');
//$routes->get('/cotizaciones/crear', 'CotizacionController::crear');

// ============================================================================
// Sección de rutas API REST
// ============================================================================

// Prefijo base para todas las rutas de la API
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // ── Clientes ─────────────────────────────────────────────────────────────
    $routes->get   ('clientes',     'ClientesApi::index');
    $routes->get   ('clientes/(:num)', 'ClientesApi::show/$1');
    $routes->post  ('clientes',     'ClientesApi::create');
    $routes->put   ('clientes/(:num)', 'ClientesApi::update/$1');
    $routes->delete('clientes/(:num)', 'ClientesApi::delete/$1');

    // ── Cotizaciones ──────────────────────────────────────────────────────────
    $routes->get   ('cotizaciones',              'CotizacionesApi::index');
    $routes->get   ('cotizaciones/(:num)',        'CotizacionesApi::show/$1');
    $routes->post  ('cotizaciones',              'CotizacionesApi::create');
    $routes->put   ('cotizaciones/(:num)',        'CotizacionesApi::update/$1');
    $routes->patch ('cotizaciones/(:num)/estado', 'CotizacionesApi::cambiarEstado/$1');
    $routes->delete('cotizaciones/(:num)',        'CotizacionesApi::delete/$1');

    // ── Contratos ─────────────────────────────────────────────────────────────
    $routes->get   ('contratos',              'ContratosApi::index');
    $routes->get   ('contratos/(:num)',        'ContratosApi::show/$1');
    $routes->post  ('contratos',              'ContratosApi::create');
    $routes->patch ('contratos/(:num)',        'ContratosApi::update/$1');
    $routes->patch ('contratos/(:num)/estado', 'ContratosApi::cambiarEstado/$1');

    // ── Pagos ─────────────────────────────────────────────────────────────────
    $routes->get   ('pagos',         'PagosApi::index');       // ?contrato=1
    $routes->get   ('pagos/(:num)',  'PagosApi::show/$1');
    $routes->post  ('pagos',         'PagosApi::create');
    $routes->delete('pagos/(:num)',  'PagosApi::delete/$1');
    $routes->get   ('formas-pago',   'PagosApi::formasPago');

    // ── Paquetes ──────────────────────────────────────────────────────────────
    $routes->get   ('paquetes',              'PaquetesApi::index');
    $routes->get   ('paquetes/(:num)',        'PaquetesApi::show/$1');
    $routes->post  ('paquetes',              'PaquetesApi::create');
    $routes->put   ('paquetes/(:num)',        'PaquetesApi::update/$1');
    $routes->patch ('paquetes/(:num)/estado', 'PaquetesApi::cambiarEstado/$1');
    // Gestión de productos dentro del paquete
    $routes->post  ('paquetes/(:num)/productos',         'PaquetesApi::agregarProducto/$1');
    $routes->delete('paquetes/(:num)/productos/(:num)',   'PaquetesApi::quitarProducto/$1/$2');

    // ── Promociones Escolares ─────────────────────────────────────────────────
    $routes->get   ('promociones',              'PromocionesApi::index');
    $routes->get   ('promociones/(:num)',        'PromocionesApi::show/$1');
    $routes->post  ('promociones',              'PromocionesApi::create');
    $routes->put   ('promociones/(:num)',        'PromocionesApi::update/$1');
    $routes->patch ('promociones/(:num)/activar','PromocionesApi::toggleActiva/$1');
});
