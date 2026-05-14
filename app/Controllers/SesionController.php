<?php

namespace App\Controllers;

use App\Services\Contratos\ContratoService;
use Config\Database;

class SesionController extends BaseController
{
    public function lista()
    {
        $data = [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ];
        return view('sesiones/lista', $data);
    }

    public function index(int $idContrato)
    {
        $db             = Database::connect();
        $contratoService = new ContratoService();

        $contrato = $contratoService->buscarPorID($idContrato);
        if (!$contrato) {
            return view('errors/html/error_404', ['message' => 'Contrato no encontrado']);
        }

        // Promociones ligadas a la cotización del contrato
        $promociones = $db->table('promociones_escolares pe')
            ->select('pe.*, c.nombre_colegio')
            ->join('colegios c', 'c.id_colegio = pe.id_colegio')
            ->where('pe.id_cotizacion', $contrato['id_cotizacion'])
            ->orderBy('pe.nombre', 'ASC')
            ->get()->getResultArray();

        // Configuración de sesiones de los paquetes contratados
        $detalles = $db->table('cotizaciones_detalles')
            ->where('id_cotizacion', $contrato['id_cotizacion'])
            ->where('tipo_item', 'paquete')
            ->get()->getResultArray();

        $idsPaquetes = array_column($detalles, 'id_referencia');
        $sesionesConfig = [];

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
