<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================================
// Formulario público (sin auth — acceso por token único)
// ============================================================================

$routes->get ('formulario/gracias',              'FormularioController::gracias');
$routes->get ('formulario/stock/(:num)',         'FormularioController::stock/$1');
$routes->get ('formulario/grupo/(:segment)',     'FormularioController::grupoIndex/$1');
$routes->post('formulario/grupo/guardar',        'FormularioController::grupoGuardar');
$routes->get ('formulario/(:segment)',           'FormularioController::index/$1');
$routes->post('formulario/guardar',              'FormularioController::guardar');

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
    $routes->get('/cotizaciones/(:num)',          'CotizacionController::ver/$1');
    $routes->get('/contratos',                      'ContratoController::index');
    $routes->get('/contratos/crear',               'ContratoController::crear');
    $routes->get('/contratos/(:num)',              'ContratoController::generarContrato/$1');
    $routes->get('/promociones',                     'PromocionController::index');
    $routes->get('/sesiones',                        'SesionController::lista');
    $routes->get('/contratos/(:num)/sesiones',     'SesionController::index/$1');
    $routes->get('/contratos/(:num)/sesiones/(:num)/lista-pdf', 'SesionController::imprimirLista/$1/$2');
    $routes->get('/catalogo',              'PaqueteController::index');
    $routes->get('/clientes',              'ClienteController::index');
    $routes->get('/calendario',            'CalendarioController::index');

    // ── Admin formularios ─────────────────────────────────────────────────────
    $routes->group('admin/formularios', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get ('/',                    'AdminController::index');
        $routes->post('/',                    'AdminController::crear');
        $routes->get ('promocion/(:num)',     'AdminController::promocion/$1');
        $routes->patch('promocion/(:num)',    'AdminController::actualizarPromocion/$1');
        $routes->delete('promocion/(:num)',   'AdminController::eliminarPromocion/$1');
        $routes->post('alumno/agregar',       'AdminController::agregarAlumno');
        $routes->post('alumno/importar',       'AdminController::importarAlumnos');
        $routes->post('alumno/agregar-completo', 'AdminController::agregarCompleto');
        $routes->get ('exportar/(:num)',      'AdminController::exportarCsv/$1');
        $routes->post('vincular/(:num)',      'AdminController::vincularPromocion/$1');
        $routes->get ('promo-escolar/(:num)', 'AdminController::irDesdePromocion/$1');
    });

});

// ============================================================================
// API REST (protegidas — devuelven 401 JSON si no hay sesión)
// ============================================================================

$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'auth'], function ($routes) {

    // ── Clientes (personas de contacto) ─────────────────────────────────────
    $routes->get   ('clientes',        'ClientesApi::index');
    $routes->get   ('clientes/(:num)', 'ClientesApi::show/$1');
    $routes->post  ('clientes',        'ClientesApi::create');
    $routes->put   ('clientes/(:num)', 'ClientesApi::update/$1');
    $routes->delete('clientes/(:num)', 'ClientesApi::delete/$1');

    // ── Colegios (entidad "cliente" desde la UI) ──────────────────────────────
    $routes->get('colegios',        'ColegiosApi::index');
    $routes->get('colegios/(:num)', 'ColegiosApi::show/$1');
    $routes->put('colegios/(:num)', 'ColegiosApi::update/$1');

    // ── Cotizaciones ──────────────────────────────────────────────────────────
    $routes->get   ('cotizaciones',               'CotizacionesApi::index');
    $routes->get   ('cotizaciones/(:num)',         'CotizacionesApi::show/$1');
    $routes->post  ('cotizaciones',               'CotizacionesApi::create');
    $routes->put   ('cotizaciones/(:num)',         'CotizacionesApi::update/$1');
    $routes->patch ('cotizaciones/(:num)/estado',   'CotizacionesApi::cambiarEstado/$1');
    $routes->patch ('cotizaciones/(:num)/archivar', 'CotizacionesApi::archivar/$1');
    $routes->delete('cotizaciones/(:num)',          'CotizacionesApi::delete/$1');

    // ── Contratos ─────────────────────────────────────────────────────────────
    $routes->get   ('contratos',               'ContratosApi::index');
    $routes->get   ('contratos/(:num)',         'ContratosApi::show/$1');
    $routes->post  ('contratos',               'ContratosApi::create');
    $routes->patch ('contratos/(:num)',         'ContratosApi::update/$1');
    $routes->patch ('contratos/(:num)/estado',   'ContratosApi::cambiarEstado/$1');
    $routes->patch ('contratos/(:num)/archivar', 'ContratosApi::archivar/$1');

    // ── Pagos ─────────────────────────────────────────────────────────────────
    $routes->get   ('pagos',          'PagosApi::index');
    $routes->get   ('pagos/(:num)',   'PagosApi::show/$1');
    $routes->post  ('pagos',          'PagosApi::create');
    $routes->delete('pagos/(:num)',   'PagosApi::delete/$1');
    $routes->get   ('reniec/dni',     'ReniecApi::dni');

    // ── Paquetes ──────────────────────────────────────────────────────────────
    $routes->get   ('paquetes',               'PaquetesApi::index');
    $routes->get   ('paquetes/(:num)',         'PaquetesApi::show/$1');
    $routes->post  ('paquetes',               'PaquetesApi::create');
    $routes->put   ('paquetes/(:num)',         'PaquetesApi::update/$1');
    $routes->patch ('paquetes/(:num)/estado',  'PaquetesApi::cambiarEstado/$1');
    $routes->post  ('paquetes/(:num)/productos',       'PaquetesApi::agregarProducto/$1');
    $routes->delete('paquetes/(:num)/productos/(:num)', 'PaquetesApi::quitarProducto/$1/$2');
    $routes->post  ('paquetes/(:num)/imagen',          'PaquetesApi::subirImagen/$1');
    $routes->delete('paquetes/(:num)/imagen',                  'PaquetesApi::eliminarImagen/$1');
    $routes->get   ('productos',                               'PaquetesApi::indexProductos');

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
    $routes->get   ('estudiantes/(:num)', 'EstudiantesApi::show/$1');
    $routes->post  ('estudiantes',        'EstudiantesApi::create');
    $routes->put   ('estudiantes/(:num)', 'EstudiantesApi::update/$1');
    $routes->delete('estudiantes/(:num)', 'EstudiantesApi::delete/$1');
});
