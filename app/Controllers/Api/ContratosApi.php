<?php

/**
 * @file    ContratosApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión de contratos.
 * Delega la lógica de negocio en ContratoService y formatea
 * las respuestas con ContratoTransformer.
 *
 * Endpoints:
 *   GET    /api/contratos             → listar con filtros opcionales
 *   GET    /api/contratos/{id}        → detalle + pagos + saldo
 *   POST   /api/contratos             → crear (requiere cotización APROBADA)
 *   PATCH  /api/contratos/{id}/estado → cambiar estado
 *   PATCH  /api/contratos/{id}        → actualizar datos del contrato
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Services\Contratos\ContratoService;
use App\Transformers\ContratoTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Contratos.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class ContratosApi extends BaseApiController
{
    /** @var ContratoService Servicio con la lógica de negocio de contratos. */
    protected ContratoService $contratoService;

    /** @var ContratoTransformer Formateador de respuestas JSON. */
    protected ContratoTransformer $contratoTransformer;

    public function __construct()
    {
        $this->contratoService     = new ContratoService();
        $this->contratoTransformer = new ContratoTransformer();
    }

    /**
     * GET /api/contratos[?estado=ACTIVO]
     *
     * @return ResponseInterface 200 con la lista de contratos.
     */
    public function index()
    {
        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->contratoTransformer->transformMany(
                    $this->contratoService->listar()
                ),
            ]);
    }

    /**
     * GET /api/contratos/{id}
     *
     * Incluye el historial de pagos y el saldo pendiente calculado.
     *
     * @param  int $id ID del contrato.
     * @return ResponseInterface 200 con detalle | 404 si no existe.
     */
    public function show(int $id)
    {
        $contrato = $this->contratoService->buscarPorID($id);

        if (!$contrato) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Contrato no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->contratoTransformer->transform($contrato),
            ]);
    }

    /**
     * POST /api/contratos
     *
     * Body: { id_cotizacion, adelanto, observaciones? }
     *
     * @return ResponseInterface 201 con id_contrato, total y saldo | 409 si la cotización no es válida.
     */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'id_cotizacion' => 'required|integer',
            'adelanto'      => 'required|decimal|greater_than[0]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $result = $this->contratoService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(array_merge(['status' => 'success', 'message' => 'Contrato creado'], $result));
    }

    /**
     * PATCH /api/contratos/{id}/estado
     *
     * Body: { estado: "ACTIVO" | "CANCELADO" | "COMPLETADO" }
     *
     * @param  mixed $id ID del contrato.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function cambiarEstado($id)
    {
        $body    = $this->request->getJSON(true) ?? [];
        $estado  = strtoupper($body['estado'] ?? '');
        $validos = ['ACTIVO', 'CANCELADO', 'COMPLETADO'];

        if (!in_array($estado, $validos)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ' . implode(', ', $validos)]);
        }

        try {
            $this->contratoService->cambiarEstado((int) $id, $estado);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Estado del contrato cambiado a {$estado}"]);
    }

    /**
     * PATCH /api/contratos/{id}/archivar
     *
     * Alterna el flag `archivado` del contrato. No permitido en ACTIVO.
     *
     * @param  mixed $id ID del contrato.
     * @return ResponseInterface 200 | 404 | 409.
     */
    public function archivar($id)
    {
        try {
            $archivado = $this->contratoService->archivar((int) $id);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'    => 'success',
                'archivado' => $archivado,
                'message'   => $archivado ? 'Contrato archivado.' : 'Contrato desarchivado.',
            ]);
    }

    /**
     * PATCH /api/contratos/{id}
     *
     * Body: { adelanto?, fecha_emision?, observaciones? }
     *
     * @param  int $id ID del contrato.
     * @return ResponseInterface 200 | 404 | 409 | 422.
     */
    public function update(int $id)
    {
        $body = $this->request->getJSON(true) ?? [];

        try {
            $this->contratoService->actualizar($id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Contrato actualizado']);
    }

}
