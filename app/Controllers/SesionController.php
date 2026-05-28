<?php

/**
 * @file    SesionController.php
 * @package App\Controllers
 *
 * Controlador web para el módulo de Sesiones Fotográficas.
 * Gestiona la vista detallada de sesiones asociadas a un contrato específico.
 */

namespace App\Controllers;

use App\Services\Contratos\ContratoService;
use Config\Database;

/**
 * Sirve las vistas del módulo de sesiones fotográficas.
 *
 * Rutas:
 *  - GET /contratos/{idContrato}/sesiones → index(int $idContrato)
 */
class SesionController extends BaseController
{
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
    public function lista()
    {
        return view('sesiones/lista', [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ]);
    }

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
