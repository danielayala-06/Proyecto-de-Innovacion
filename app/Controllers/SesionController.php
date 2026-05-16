<?php

/**
 * @file    SesionController.php
 * @package App\Controllers
 *
 * Controlador web para el módulo de Sesiones Fotográficas.
 * Gestiona dos vistas: el listado global de sesiones y la vista detallada
 * de sesiones asociadas a un contrato específico.
 */

namespace App\Controllers;

use App\Services\Contratos\ContratoService;
use Config\Database;

/**
 * Sirve las vistas del módulo de sesiones fotográficas.
 *
 * Rutas:
 *  - GET /sesiones                        → lista()
 *  - GET /contratos/{idContrato}/sesiones → index(int $idContrato)
 */
class SesionController extends BaseController
{
    /**
     * Renderiza el listado global de sesiones fotográficas.
     *
     * La carga de datos y los filtros se manejan desde el módulo JS
     * a través de la API.
     *
     * @return string HTML de la vista renderizada.
     */
    public function lista()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('sesiones/lista', $data);
    }

    /**
     * Renderiza la vista de gestión de sesiones para un contrato específico.
     *
     * Consultas realizadas:
     *  - Obtiene el contrato completo vía ContratoService (incluye id_cotizacion).
     *  - Carga las promociones escolares ligadas a esa cotización (JOIN colegios).
     *  - Carga la configuración de sesiones de los paquetes contratados
     *    (JOIN paquetes_sesiones → paquetes) para mostrar los tipos disponibles.
     *
     * Si el contrato no existe, retorna la vista de error 404.
     *
     * @param  int    $idContrato ID del contrato cuyas sesiones se gestionarán.
     * @return string HTML de la vista renderizada o vista de error 404.
     */
    public function index(int $idContrato)
    {
        $db              = Database::connect();
        $contratoService = new ContratoService();

        $contrato = $contratoService->buscarPorID($idContrato);
        if (!$contrato) {
            return view('errors/html/error_404', ['message' => 'Contrato no encontrado']);
        }

        // Promociones asociadas a la cotización del contrato (con nombre de colegio)
        $promociones = $db->table('promociones_escolares pe')
            ->select('pe.*, c.nombre_colegio')
            ->join('colegios c', 'c.id_colegio = pe.id_colegio')
            ->where('pe.id_cotizacion', $contrato['id_cotizacion'])
            ->orderBy('pe.nombre', 'ASC')
            ->get()->getResultArray();

        // IDs de paquetes incluidos en la cotización del contrato
        $detalles = $db->table('cotizaciones_detalles')
            ->where('id_cotizacion', $contrato['id_cotizacion'])
            ->where('tipo_item', 'paquete')
            ->get()->getResultArray();

        $idsPaquetes    = array_column($detalles, 'id_referencia');
        $sesionesConfig = [];

        // Configuración de sesiones solo si hay paquetes (evita WHERE IN vacío)
        if (!empty($idsPaquetes)) {
            $sesionesConfig = $db->table('paquetes_sesiones ps')
                ->select('ps.*, p.nombre_paquete')
                ->join('paquetes p', 'p.id_paquete = ps.id_paquete')
                ->whereIn('ps.id_paquete', $idsPaquetes)
                ->get()->getResultArray();
        }

        $data = [
            'header'         => view('Layouts/header'),
            'footer'         => view('Layouts/footer'),
            'contrato'       => $contrato,
            'promociones'    => $promociones,
            'sesionesConfig' => $sesionesConfig,
        ];

        return view('sesiones/index', $data);
    }
}
