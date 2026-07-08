<?php

namespace App\Controllers;

use App\Services\Contratos\ContratoService;
use App\Services\Sesiones\SesionService;
use Dompdf\Dompdf;
use Dompdf\Options;

class SesionController extends BaseController
{
    private SesionService   $sesionService;
    private ContratoService $contratoService;

    public function __construct()
    {
        $this->sesionService   = new SesionService();
        $this->contratoService = new ContratoService();
    }

    public function lista(): string
    {
        return view('sesiones/lista', [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ]);
    }

    public function index(int $idContrato)
    {
        $contrato = $this->contratoService->buscarPorID($idContrato);
        if (!$contrato) {
            return view('errors/html/error_404', ['message' => 'Contrato no encontrado']);
        }

        $datos = $this->sesionService->datosPorContrato((int) $contrato['id_cotizacion']);

        return view('sesiones/index', [
            'header'         => view('Layouts/header'),
            'footer'         => view('Layouts/footer'),
            'contrato'       => $contrato,
            'promociones'    => $datos['promociones'],
            'sesionesConfig' => $datos['sesionesConfig'],
        ]);
    }

    public function imprimirLista(int $idContrato, int $idPromocion)
    {
        $contrato = $this->contratoService->buscarPorID($idContrato);
        if (!$contrato) {
            return view('errors/html/error_404', ['message' => 'Contrato no encontrado']);
        }

        $datos = $this->sesionService->datosListaPdf($idPromocion, (int) $contrato['id_cotizacion']);
        if (empty($datos)) {
            return view('errors/html/error_404', ['message' => 'Promoción no encontrada para este contrato']);
        }

        $titulo = sprintf(
            'lista_estudiantes_%s_%s',
            preg_replace('/[^A-Za-z0-9_-]/', '_', $datos['promocion']['nombre_colegio'] ?? 'promocion'),
            $idPromocion
        );

        $html = view('pdf/sesiones_lista', [
            'titulo'      => $titulo,
            'promocion'   => $datos['promocion'],
            'contrato'    => $contrato,
            'estudiantes' => $datos['estudiantes'],
            'sesiones'    => $datos['sesiones'],
            'asistencia'  => $datos['asistencia'],
            'productos'   => $datos['productos'],
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled',    false);
        $options->set('defaultFont',        'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled',       false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $titulo . '.pdf"')
            ->setBody($dompdf->output());
    }
}
