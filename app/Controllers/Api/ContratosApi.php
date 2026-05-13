<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\Contratos\ContratoService;
use App\Transformers\ContratoTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ContratosApi
 * Base URL: /api/contratos
 *
 * GET    /api/contratos              → listar con filtros
 * GET    /api/contratos/{id}         → detalle + pagos asociados
 * POST   /api/contratos              → crear (requiere cotización APROBADA)
 * PATCH  /api/contratos/{id}/estado  → cambiar estado
 * PATCH  /api/contratos/{id}         → actualizar datos
 */
class ContratosApi extends BaseController
{
    protected ContratoService     $contratoService;
    protected ContratoTransformer $contratoTransformer;

    public function __construct()
    {
        $this->contratoService     = new ContratoService();
        $this->contratoTransformer = new ContratoTransformer();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/contratos[?estado=ACTIVO]
    // ────────────────────────────────────────────────────────────────────────
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

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/contratos/{id}
    // ────────────────────────────────────────────────────────────────────────
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

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/contratos
    // Body: { id_cotizacion, adelanto, observaciones? }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'id_cotizacion' => 'required|integer',
            'adelanto'      => 'required|decimal|greater_than_equal_to[0]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $result = $this->contratoService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(array_merge(['status' => 'success', 'message' => 'Contrato creado'], $result));
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/contratos/{id}/estado
    // Body: { estado: "ACTIVO" | "CANCELADO" | "COMPLETADO" }
    // ────────────────────────────────────────────────────────────────────────
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
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Estado del contrato cambiado a {$estado}"]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/contratos/{id}
    // Body: { adelanto?, fecha_emision?, observaciones? }
    // ────────────────────────────────────────────────────────────────────────
    public function update(int $id)
    {
        $body = $this->request->getJSON(true) ?? [];

        try {
            $this->contratoService->actualizar($id, $body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Contrato actualizado']);
    }

    private function _serviceError(\RuntimeException $e): \CodeIgniter\HTTP\ResponseInterface
    {
        $code   = (int) $e->getCode() ?: 500;
        $errors = ($code === 422) ? json_decode($e->getMessage(), true) : null;

        $httpStatus = match ($code) {
            404     => ResponseInterface::HTTP_NOT_FOUND,
            409     => ResponseInterface::HTTP_CONFLICT,
            422     => ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
            default => ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
        };

        return $this->response
            ->setStatusCode($httpStatus)
            ->setJSON(is_array($errors)
                ? ['status' => 'error', 'errors'  => $errors]
                : ['status' => 'error', 'message' => $e->getMessage()]
            );
    }
}
