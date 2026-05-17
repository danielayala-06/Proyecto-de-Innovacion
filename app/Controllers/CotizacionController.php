<?php

/**
 * @file    CotizacionController.php
 * @package App\Controllers
 *
 * Controlador web para el módulo de Cotizaciones.
 * Renderiza las vistas de listado y creación de cotizaciones; toda la
 * lógica CRUD es manejada por el módulo JS que consume la API REST.
 */

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * Sirve las vistas del módulo de cotizaciones.
 *
 * Rutas:
 *  - GET /cotizaciones                → index()
 *  - GET /cotizaciones/crear          → crear()
 *  - GET /cotizaciones/editar/{id}    → editar()
 */
class CotizacionController extends BaseController
{
    /**
     * Renderiza la vista del listado de cotizaciones.
     *
     * @return string HTML de la vista renderizada.
     */
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('cotizaciones/index', $data);
    }

    /**
     * Renderiza la vista del formulario de creación de cotizaciones.
     *
     * Inyecta el `id_usuario` de la sesión activa en la vista para que
     * el módulo JS lo use al enviar la nueva cotización a la API.
     *
     * @return string HTML de la vista renderizada.
     *
     * @todo Reemplazar el valor hardcodeado de `id_usuario` por
     *       `session()->get('usuario_id')` cuando el flujo de sesión esté validado.
     */
    public function crear()
    {
        $data = [
            'header'     => view('Layouts/header'),
            'footer'     => view('Layouts/footer'),
            'id_usuario' => 1, // TODO: reemplazar por session()->get('usuario_id')
        ];

        return view('cotizaciones/crear', $data);
    }

    /**
     * Renderiza la vista del formulario de edición de una cotización existente.
     *
     * Solo cotizaciones en estado PENDIENTE pueden editarse; la validación
     * la hace el módulo JS al cargar la cotización desde la API.
     *
     * @param  int    $id ID de la cotización a editar.
     * @return string HTML de la vista renderizada.
     */
    public function editar(int $id)
    {
        $data = [
            'header'        => view('Layouts/header'),
            'footer'        => view('Layouts/footer'),
            'id_cotizacion' => $id,
        ];

        return view('cotizaciones/editar', $data);
    }
}
