<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');


// ============================================================================
// Sección de rutas API REST
// ============================================================================

// Prefijo base para todas las rutas de la API
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // ── Clientes ─────────────────────────────────────────────────────────────
    $routes->get   ('clientes',     'ClientesController::index');
    $routes->get   ('clientes/(:num)', 'ClientesController::show/$1');
    $routes->post  ('clientes',     'ClientesController::create');
    $routes->put   ('clientes/(:num)', 'ClientesController::update/$1');
    $routes->delete('clientes/(:num)', 'ClientesController::delete/$1');

    // ── Cotizaciones ──────────────────────────────────────────────────────────
    $routes->get   ('cotizaciones',              'CotizacionesController::index');
    $routes->get   ('cotizaciones/(:num)',        'CotizacionesController::show/$1');
    $routes->post  ('cotizaciones',              'CotizacionesController::create');
    $routes->put   ('cotizaciones/(:num)',        'CotizacionesController::update/$1');
    $routes->patch ('cotizaciones/(:num)/estado', 'CotizacionesController::cambiarEstado/$1');
    $routes->delete('cotizaciones/(:num)',        'CotizacionesController::delete/$1');

    // ── Contratos ─────────────────────────────────────────────────────────────
    $routes->get   ('contratos',              'ContratosController::index');
    $routes->get   ('contratos/(:num)',        'ContratosController::show/$1');
    $routes->post  ('contratos',              'ContratosController::create');
    $routes->patch ('contratos/(:num)/estado', 'ContratosController::cambiarEstado/$1');

    // ── Pagos ─────────────────────────────────────────────────────────────────
    $routes->get   ('pagos',        'PagosController::index');       // ?contrato=1
    $routes->get   ('pagos/(:num)', 'PagosController::show/$1');
    $routes->post  ('pagos',        'PagosController::create');
    $routes->delete('pagos/(:num)', 'PagosController::delete/$1');

    // ── Paquetes ──────────────────────────────────────────────────────────────
    $routes->get   ('paquetes',              'PaquetesController::index');
    $routes->get   ('paquetes/(:num)',        'PaquetesController::show/$1');
    $routes->post  ('paquetes',              'PaquetesController::create');
    $routes->put   ('paquetes/(:num)',        'PaquetesController::update/$1');
    $routes->patch ('paquetes/(:num)/estado', 'PaquetesController::cambiarEstado/$1');
    // Gestión de productos dentro del paquete
    $routes->post  ('paquetes/(:num)/productos',         'PaquetesController::agregarProducto/$1');
    $routes->delete('paquetes/(:num)/productos/(:num)',   'PaquetesController::quitarProducto/$1/$2');

    // ── Promociones Escolares ─────────────────────────────────────────────────
    $routes->get   ('promociones',              'PromocionesController::index');
    $routes->get   ('promociones/(:num)',        'PromocionesController::show/$1');
    $routes->post  ('promociones',              'PromocionesController::create');
    $routes->put   ('promociones/(:num)',        'PromocionesController::update/$1');
    $routes->patch ('promociones/(:num)/activar','PromocionesController::toggleActiva/$1');
});
