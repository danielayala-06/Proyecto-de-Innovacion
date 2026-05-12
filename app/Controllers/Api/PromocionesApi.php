<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\Promociones\PromocionService;
use App\Transformers\PromocionTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PromocionesApi
 * Base URL: /api/promociones
 *
 * GET    /api/promociones                  → listar con filtros
 * GET    /api/promociones/{id}             → detalle + estudiantes + sesiones
 * POST   /api/promociones                  → crear
 * PUT    /api/promociones/{id}             → actualizar
 * PATCH  /api/promociones/{id}/activar     → activar / desactivar
 */
class PromocionesApi extends BaseController
{
    protected PromocionService     $promocionService;
    protected PromocionTransformer $promocionTransformer;

    public function __construct()
    {
        $this->promocionService     = new PromocionService();
        $this->promocionTransformer = new PromocionTransformer();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/promociones[?colegio=1&anio=2026&activa=1]
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $filters = array_filter([
            'colegio' => $this->request->getGet('colegio'),
            'anio'    => $this->request->getGet('anio'),
            'activa'  => $this->request->getGet('activa'),
        ], fn($v) => $v !== null);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->promocionTransformer->transformMany(
                    $this->promocionService->listar($filters)
                ),
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/promociones/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $promocion = $this->promocionService->obtenerPorId((int) $id);

        if (!$promocion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Promoción no encontrada']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->promocionTransformer->transform($promocion),
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/promociones
    // Body: { id_colegio, id_cotizacion, nombre, grado, seccion?, num_estudiantes, anio? }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'id_colegio'      => 'required|integer',
            'id_cotizacion'   => 'required|integer',
            'nombre'          => 'required|max_length[100]',
            'grado'           => 'required|max_length[10]',
            'num_estudiantes' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $idPromocion = $this->promocionService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Promoción creada', 'id_promocion' => $idPromocion]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PUT /api/promociones/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'grado'           => 'permit_empty|max_length[10]',
            'num_estudiantes' => 'permit_empty|integer|greater_than[0]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $this->promocionService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Promoción actualizada']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/promociones/{id}/activar
    // Body: { is_active: true | false }
    // ────────────────────────────────────────────────────────────────────────
    public function toggleActiva($id)
    {
        $body     = $this->request->getJSON(true) ?? [];
        $isActive = isset($body['is_active']) ? (bool) $body['is_active'] : null;

        try {
            $nuevo = $this->promocionService->toggleActiva((int) $id, $isActive);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'    => 'success',
                'message'   => 'Promoción ' . ($nuevo ? 'activada' : 'desactivada'),
                'is_active' => $nuevo,
            ]);
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
