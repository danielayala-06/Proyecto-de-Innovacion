<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ContratosModel;
use App\Services\Contratos\ContratoService;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
class ContratoController extends BaseController
{
    public function index()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];

        return view('contratos/index', $data);
    }

    public function generarPDF(int $id)
    {
        $contratoService  = new ContratoService();

        $contrato = $contratoService->buscarPorID($id) ?? null;

        if(!$contrato)return view('errors/html/error_404', ['message' =>'Contrato no encontrado']);

        // Creamos el titulo del contrato:
        $titulo =  'contrato-'.
            str_pad(date('y'), 4, "0", STR_PAD_LEFT).'-'.
            str_pad($contrato['id_contrato'], 4, "0", STR_PAD_LEFT);

        // Vamos a crear el contrato:
        /**
        * RENDER VIEW HTML
        */
        $html = view(
            'pdf/contrato',
            [
                'contrato' => $contrato,
                'titulo' => $titulo
            ]
        );

        //$html = '<h1>Hola mundo como estan causas</h1>';
        /**
         * CONFIG DOMPDF
         */
        $options = new Options();

        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times New Roman');
        $options->set('defaultPaperSize', 'letter');

        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);


        /**
         * LOAD HTML
         */
        $dompdf->loadHtml($html);

        /**
         * PAPER
         */
        $dompdf->setPaper(
            'A4',
            'portrait'
        );

        /**
         * RENDER PDF
         */
        $dompdf->render();

        /**
         * STREAM PDF
         */
        $dompdf->stream(
            $titulo . '.pdf',
            [
                'Attachment' => false
            ]
        );

        return;
    }
}
