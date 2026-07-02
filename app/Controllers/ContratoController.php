<?php

/**
 * @file    ContratoController.php
 * @package App\Controllers
 *
 * Controlador web para el módulo de Contratos.
 * Gestiona la renderización de vistas y la generación de contratos en PDF
 * mediante la librería Dompdf.
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Contratos\ContratoService;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Sirve las vistas del módulo de contratos y genera PDFs bajo demanda.
 *
 * Rutas:
 *  - GET /contratos                → index()
 *  - GET /contratos/crear          → crear()
 *  - GET /contratos/{id}/pdf       → generarContrato(int $id)
 */
class ContratoController extends BaseController
{
    /**
     * Renderiza la vista principal del listado de contratos.
     *
     * @return string HTML de la vista renderizada.
     */
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('contratos/index', $data);
    }

    /**
     * Renderiza la vista del formulario de creación de contratos.
     *
     * @return string HTML de la vista renderizada.
     */
    public function crear()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('contratos/crear', $data);
    }

    /**
     * Genera y transmite el PDF del contrato especificado.
     *
     * Proceso:
     *  1. Obtiene el contrato completo (con cliente, promociones y pagos) vía ContratoService.
     *  2. Construye el título con formato: `contrato-AAAA-NNNN`.
     *  3. Renderiza la vista HTML `pdf/contrato` con los datos del contrato.
     *  4. Convierte el HTML a PDF con Dompdf (papel A4 portrait, fuente Times New Roman).
     *  5. Transmite el PDF al navegador como visualización inline (sin descarga forzada).
     *
     * Si el contrato no existe, retorna la vista de error 404.
     *
     * @param  int $id ID del contrato a generar.
     * @return void|string Transmite el PDF o retorna la vista de error 404.
     */
    public function generarContrato(int $id)
    {
        // Obtenemos la data del contrato
        $contratoService = new ContratoService();
        $contrato        = $contratoService->obtenerDataContratoPDF($id)?? null;

        if (!$contrato) {
            return view('errors/html/error_404', ['message' => 'Contrato no encontrado']);
        }

        // Nombre del archivo basado en el cliente/colegio
        $promo = $contrato['promociones'][0] ?? null;
        if ($promo && !empty($promo['nombre_colegio'])) {
            $partes = [strtoupper($promo['nombre_colegio'])];
            if (!empty($promo['grado']))   $partes[] = $promo['grado'];
            if (!empty($promo['seccion'])) $partes[] = $promo['seccion'];
            $partes[] = !empty($contrato['fecha_creacion']) ? substr($contrato['fecha_creacion'], 0, 4) : date('Y');
            $titulo = implode('_', $partes);
        } else {
            $titulo = $contrato['cliente'] ?? ('contrato-' . $contrato['id_contrato']);
        }
        // Eliminar caracteres no válidos en nombres de archivo
        $titulo = preg_replace('/[\/\\\\:*?"<>|]/', '', $titulo);

        // Renderiza la plantilla HTML del contrato
        $html = view('pdf/contrato', [
            'contrato' => $contrato,
            'titulo'   => $titulo,
        ]);

        // Configuración de Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled',    false);
        $options->set('defaultFont',        'Times New Roman');
        $options->set('defaultPaperSize',   'letter');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled',       false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Attachment => false: el PDF se abre en el navegador, no se descarga
        // exit() detiene el ciclo de CI4 para que no intente enviar cookies después del stream
        ob_end_clean();
        $dompdf->stream($titulo . '.pdf', ['Attachment' => false]);
        exit();
    }
}
