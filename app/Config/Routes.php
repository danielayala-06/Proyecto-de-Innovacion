<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================================
// Autenticación (públicas — sin filtro auth)
// ============================================================================

$routes->get ('/login',  'AuthController::login');
$routes->post('/login',  'AuthController::authenticate');
$routes->get ('/logout', 'AuthController::logout');

// ============================================================================
// Vistas protegidas (requieren sesión activa)
// ============================================================================

$routes->group('', ['filter' => 'auth'], function ($routes) {

    $routes->get('/',                      'Home::index');
    $routes->get('/cotizaciones',                  'CotizacionController::index');
    $routes->get('/cotizaciones/crear',           'CotizacionController::crear');
    $routes->get('/cotizaciones/editar/(:num)',   'CotizacionController::editar/$1');
    $routes->get('/contratos',                      'ContratoController::index');
    $routes->get('/contratos/crear',               'ContratoController::crear');
    $routes->get('/contratos/(:num)',              'ContratoController::generarContrato/$1');
    $routes->get('/sesiones',                       'SesionController::lista');
    $routes->get('/contratos/(:num)/sesiones',     'SesionController::index/$1');
    $routes->get('/paquetes',              'PaqueteController::index');
    $routes->get('/clientes',              'ClienteController::index');
    $routes->get('/calendario',            'CalendarioController::index');

});

// ============================================================================
// API REST (protegidas — devuelven 401 JSON si no hay sesión)
// ============================================================================

$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'auth'], function ($routes) {

    // ── Clientes ─────────────────────────────────────────────────────────────
    $routes->get   ('clientes',        'ClientesApi::index');
    $routes->get   ('clientes/(:num)', 'ClientesApi::show/$1');
    $routes->post  ('clientes',        'ClientesApi::create');
    $routes->put   ('clientes/(:num)', 'ClientesApi::update/$1');
    $routes->delete('clientes/(:num)', 'ClientesApi::delete/$1');

    // ── Cotizaciones ──────────────────────────────────────────────────────────
    $routes->get   ('cotizaciones',               'CotizacionesApi::index');
    $routes->get   ('cotizaciones/(:num)',         'CotizacionesApi::show/$1');
    $routes->post  ('cotizaciones',               'CotizacionesApi::create');
    $routes->put   ('cotizaciones/(:num)',         'CotizacionesApi::update/$1');
    $routes->patch ('cotizaciones/(:num)/estado',  'CotizacionesApi::cambiarEstado/$1');
    $routes->delete('cotizaciones/(:num)',         'CotizacionesApi::delete/$1');

    // ── Contratos ─────────────────────────────────────────────────────────────
    $routes->get   ('contratos',               'ContratosApi::index');
    $routes->get   ('contratos/(:num)',         'ContratosApi::show/$1');
    $routes->post  ('contratos',               'ContratosApi::create');
    $routes->patch ('contratos/(:num)',         'ContratosApi::update/$1');
    $routes->patch ('contratos/(:num)/estado',  'ContratosApi::cambiarEstado/$1');

    // ── Pagos ─────────────────────────────────────────────────────────────────
    $routes->get   ('pagos',          'PagosApi::index');
    $routes->get   ('pagos/(:num)',   'PagosApi::show/$1');
    $routes->post  ('pagos',          'PagosApi::create');
    $routes->delete('pagos/(:num)',   'PagosApi::delete/$1');
    $routes->get   ('formas-pago',    'PagosApi::formasPago');
    $routes->get   ('reniec/dni',     'ReniecApi::dni');

    // ── Paquetes ──────────────────────────────────────────────────────────────
    $routes->get   ('paquetes',               'PaquetesApi::index');
    $routes->get   ('paquetes/(:num)',         'PaquetesApi::show/$1');
    $routes->post  ('paquetes',               'PaquetesApi::create');
    $routes->put   ('paquetes/(:num)',         'PaquetesApi::update/$1');
    $routes->patch ('paquetes/(:num)/estado',  'PaquetesApi::cambiarEstado/$1');
    $routes->post  ('paquetes/(:num)/productos',              'PaquetesApi::agregarProducto/$1');
    $routes->delete('paquetes/(:num)/productos/(:num)',        'PaquetesApi::quitarProducto/$1/$2');

    // ── Promociones Escolares ─────────────────────────────────────────────────
    $routes->get   ('promociones',               'PromocionesApi::index');
    $routes->get   ('promociones/(:num)',         'PromocionesApi::show/$1');
    $routes->post  ('promociones',               'PromocionesApi::create');
    $routes->put   ('promociones/(:num)',         'PromocionesApi::update/$1');
    $routes->patch ('promociones/(:num)/activar', 'PromocionesApi::toggleActiva/$1');

    // ── Sesiones Fotográficas ─────────────────────────────────────────────────
    $routes->get   ('sesiones',                                   'SesionesApi::index');
    $routes->get   ('sesiones/(:num)',                            'SesionesApi::show/$1');
    $routes->post  ('sesiones',                                   'SesionesApi::create');
    $routes->put   ('sesiones/(:num)',                            'SesionesApi::update/$1');
    $routes->patch ('sesiones/(:num)/estado',                     'SesionesApi::cambiarEstado/$1');
    $routes->get   ('sesiones/limite/(:num)',                     'SesionesApi::limite/$1');
    $routes->post  ('sesiones/(:num)/asistencia',                 'SesionesApi::agregarEstudiante/$1');
    $routes->delete('sesiones/(:num)/asistencia/(:num)',          'SesionesApi::quitarEstudiante/$1/$2');
    $routes->patch ('sesiones/(:num)/asistencia/(:num)',          'SesionesApi::marcarAsistencia/$1/$2');

    // ── Estudiantes ───────────────────────────────────────────────────────────
    $routes->get   ('estudiantes',        'EstudiantesApi::index');
    $routes->post  ('estudiantes',        'EstudiantesApi::create');
    $routes->put   ('estudiantes/(:num)', 'EstudiantesApi::update/$1');
    $routes->delete('estudiantes/(:num)', 'EstudiantesApi::delete/$1');
});
