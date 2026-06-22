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
use Dompdf\Dompdf;
use Dompdf\Options;

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

    public function imprimirLista(int $idContrato, int $idPromocion)
    {
        $db = Database::connect();

        $contratoService = new ContratoService();
        $contrato = $contratoService->buscarPorID($idContrato);
        if (!$contrato) {
            return view('errors/html/error_404', ['message' => 'Contrato no encontrado']);
        }

        $promocion = $db->table('promociones_escolares pe')
            ->select('pe.*, c.nombre_colegio, c.distrito, c.provincia, cotizaciones.id_cotizacion')
            ->join('colegios c', 'c.id_colegio = pe.id_colegio', 'left')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = pe.id_cotizacion', 'left')
            ->where('pe.id_promocion', $idPromocion)
            ->where('pe.id_cotizacion', $contrato['id_cotizacion'])
            ->get()
            ->getRowArray();

        if (!$promocion) {
            return view('errors/html/error_404', ['message' => 'Promoción no encontrada para este contrato']);
        }

        $estudiantes = $db->table('estudiantes e')
            ->select('e.id_estudiante, e.nombres, e.apellidos')
            ->where('e.id_promocion', $idPromocion)
            ->orderBy('e.apellidos', 'ASC')
            ->get()->getResultArray();

        $sesiones = $db->table('sesiones_fotograficas sf')
            ->select('sf.id_sesion, sf.fecha_hora_sesion, sf.tipo, sf.estado, sf.observaciones')
            ->where('sf.id_promocion', $idPromocion)
            ->orderBy('sf.fecha_hora_sesion', 'ASC')
            ->get()->getResultArray();

        $asistencia = [];
        if (!empty($sesiones)) {
            $sesionIds = array_column($sesiones, 'id_sesion');
            $rows = $db->table('sesion_asistencia sa')
                ->select('sa.id_sesion, sa.id_estudiante, sa.asistio')
                ->whereIn('sa.id_sesion', $sesionIds)
                ->get()->getResultArray();

            foreach ($rows as $row) {
                $asistencia[$row['id_sesion']][$row['id_estudiante']] = $row['asistio'];
            }
        }

        $productos = $db->table('cotizaciones_detalles cd')
            ->select([
                'cd.tipo_item',
                'cd.descripcion',
                'cd.cantidad',
                'cd.precio_unitario',
                'COALESCE(p.nombre_paquete, pr.nombre_producto, cd.descripcion) AS nombre',
            ])
            ->join('paquetes p', 'p.id_paquete = cd.id_referencia AND cd.tipo_item = "paquete"', 'left')
            ->join('productos pr', 'pr.id_producto = cd.id_referencia AND cd.tipo_item = "producto"', 'left')
            ->where('cd.id_cotizacion', $promocion['id_cotizacion'])
            ->get()->getResultArray();

        $titulo = sprintf('lista_estudiantes_%s_%s', preg_replace('/[^A-Za-z0-9_-]/', '_', $promocion['nombre_colegio'] ?? 'promocion'), $idPromocion);

        $html = view('pdf/sesiones_lista', [
            'titulo'      => $titulo,
            'promocion'   => $promocion,
            'contrato'    => $contrato,
            'estudiantes' => $estudiantes,
            'sesiones'    => $sesiones,
            'asistencia'  => $asistencia,
            'productos'   => $productos,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($titulo . '.pdf', ['Attachment' => false]);

        return;
    }
}
