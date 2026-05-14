<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\Cotizaciones\CotizacionService;
use App\Transformers\CotizacionTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CotizacionesApi
 * Base URL: /api/cotizaciones
 *
 * GET    /api/cotizaciones              → listar (paginado)
 * GET    /api/cotizaciones/{id}         → detalle + detalles
 * POST   /api/cotizaciones              → crear
 * PUT    /api/cotizaciones/{id}         → actualizar (reemplaza detalles)
 * PATCH  /api/cotizaciones/{id}/estado  → cambiar estado
 * DELETE /api/cotizaciones/{id}         → rechazar (soft)
 */
class CotizacionesApi extends BaseController
{
    protected CotizacionService     $service;
    protected CotizacionTransformer $transformer;

    public function __construct()
    {
        $this->service     = new CotizacionService();
        $this->transformer = new CotizacionTransformer();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/cotizaciones
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $sinContrato = $this->request->getGet('sin_contrato');
        $datos       = $sinContrato ? $this->service->listarDisponibles() : $this->service->listar();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->transformer->transformMany($datos),
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/cotizaciones/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id = null)
    {
        $cotizacion = $this->service->obtenerPorId((int) $id);

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->transformer->transform($cotizacion)]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/cotizaciones
    // Body: { id_cliente, id_usuario, detalles: [...], observaciones? }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'id_cliente' => 'required|integer',
            'id_usuario' => 'required|integer',
            'detalles'   => 'required',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        if (empty($body['detalles'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Debe incluir al menos un detalle']);
        }

        $body['total_estimado'] = $this->_calcularTotal($body['detalles']);

        try {
            $cotizacion = $this->service->crear($body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status'  => 'success',
                'message' => 'Cotización creada correctamente',
                'data'    => $this->transformer->transform($cotizacion),
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PUT /api/cotizaciones/{id}
    // Body: { detalles: [...], observaciones? }
    // ────────────────────────────────────────────────────────────────────────
    public function update($id = null)
    {
        $body = $this->request->getJSON(true) ?? [];

        if (!empty($body['detalles'])) {
            $body['total_estimado'] = $this->_calcularTotal($body['detalles']);
        }

        try {
            $updated = $this->service->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'  => 'success',
                'message' => 'Cotización actualizada',
                'data'    => $this->transformer->transform($updated),
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/cotizaciones/{id}/estado
    // Body: { estado: "PENDIENTE" | "APROBADA" | "RECHAZADA" }
    // ────────────────────────────────────────────────────────────────────────
    public function cambiarEstado($id = null)
    {
        $body   = $this->request->getJSON(true) ?? [];
        $estado = strtoupper($body['estado'] ?? '');
        $validos = ['PENDIENTE', 'APROBADA', 'RECHAZADA'];

        if (!in_array($estado, $validos)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ' . implode(', ', $validos)]);
        }

        $cotizacion = $this->service->obtenerPorId((int) $id);

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        $this->service->cambiarEstado((int) $id, $estado);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Estado cambiado a {$estado}"]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // DELETE /api/cotizaciones/{id}  → rechazar (no se borra físicamente)
    // ────────────────────────────────────────────────────────────────────────
    public function delete($id = null)
    {
        $cotizacion = $this->service->obtenerPorId((int) $id);

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        if ($cotizacion['cotizacion']['estado'] !== 'PENDIENTE') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'Solo se puede rechazar una cotización PENDIENTE']);
        }

        $this->service->cambiarEstado((int) $id, 'RECHAZADA');

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cotización rechazada']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ────────────────────────────────────────────────────────────────────────

    private function _calcularTotal(array $detalles): float
    {
        return (float) array_sum(
            array_map(
                fn($d) => ($d['cantidad'] ?? 1) * ($d['precio_unitario'] ?? 0),
                $detalles
            )
        );
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
