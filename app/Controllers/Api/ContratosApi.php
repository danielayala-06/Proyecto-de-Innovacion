<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Transformers\ContratoTransformer;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\contratos\ContratoService;
/**
 * ContratosApi
 * Base URL: /api/contratos
 *
 * GET    /api/contratos              → listar con filtros
 * GET    /api/contratos/{id}         → detalle + pagos asociados
 * POST   /api/contratos              → crear (requiere cotización APROBADA)
 * PATCH  /api/contratos/{id}/estado  → cambiar estado
 */
class ContratosApi extends BaseController
{
    protected ContratoService $contratoService;
    protected ContratoTransformer $contratoTransformer;

    public function __construct()
    {
        $this->contratoService = new ContratoService();
        $this->contratoTransformer = new ContratoTransformer();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/contratos[?estado=ACTIVO]
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $contratos = $this->contratoService->listar();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data' => $this->contratoTransformer->transformMany($contratos)]);
    }

    /**
     * Busca un contrato por su ID y lo devuelve.
     * @param (int)$id
     * @return ResponseInterface
     */
    public function show(int $id)
    {
        // Si no se envia el id envia un mensaje de error
        if(!$id) $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);

        // Enviamos el id al service para que el lo busque :D
        $contrato = $this->contratoService->buscarPorID($id);

        if (!$contrato) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Contrato no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data' => $this->contratoTransformer->transform($contrato)]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/contratos
    // Body: { id_cotizacion, adelanto, observaciones }
    // Requiere que la cotización esté APROBADA
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $rules = [
            'id_cotizacion' => 'required|integer',
            'adelanto'      => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $body = $this->request->getJSON(true);

        // Verificar cotización
        $cotizacion = $this->db->table('cotizaciones')
            ->where('id_cotizacion', $body['id_cotizacion'])
            ->get()->getRowArray();

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        if ($cotizacion['estado'] !== 'APROBADA') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'La cotización debe estar APROBADA para generar un contrato']);
        }

        // Verificar que no exista ya un contrato ACTIVO para esa cotización
        $contratoExistente = $this->db->table('contratos')
            ->where('id_cotizacion', $body['id_cotizacion'])
            ->where('estado', 'ACTIVO')
            ->get()->getRowArray();

        if ($contratoExistente) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'Ya existe un contrato activo para esta cotización']);
        }

        if ((float)$body['adelanto'] > (float)$cotizacion['total_estimado']) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'El adelanto no puede superar el total de la cotización']);
        }

        $this->db->table('contratos')->insert([
            'id_cotizacion' => $body['id_cotizacion'],
            'fecha_creacion'=> date('Y-m-d'),
            'fecha_emision' => $body['fecha_emision'] ?? null,
            'adelanto'      => $body['adelanto'],
            'total'         => $cotizacion['total_estimado'],
            'observaciones' => $body['observaciones'] ?? null,
            'estado'        => 'ACTIVO',
        ]);

        $idContrato = $this->db->insertID();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status'      => 'success',
                'message'     => 'Contrato creado',
                'id_contrato' => $idContrato,
                'total'       => $cotizacion['total_estimado'],
                'saldo'       => $cotizacion['total_estimado'] - $body['adelanto'],
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/contratos/{id}/estado
    // Body: { estado: "ACTIVO" | "CANCELADO" | "COMPLETADO" }
    // ────────────────────────────────────────────────────────────────────────
    public function cambiarEstado($id)
    {
        $body   = $this->request->getJSON(true);
        $estado = strtoupper($body['estado'] ?? '');
        $validos = ['ACTIVO', 'CANCELADO', 'COMPLETADO'];

        if (!in_array($estado, $validos)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ' . implode(', ', $validos)]);
        }

        $contrato = $this->db->table('contratos')->where('id_contrato', $id)->get()->getRowArray();

        if (!$contrato) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Contrato no encontrado']);
        }

        $updateData = ['estado' => $estado];
        if ($estado === 'COMPLETADO') {
            $updateData['fecha_emision'] = date('Y-m-d');
        }

        $this->db->table('contratos')->where('id_contrato', $id)->update($updateData);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Estado del contrato cambiado a {$estado}"]);
    }
}
