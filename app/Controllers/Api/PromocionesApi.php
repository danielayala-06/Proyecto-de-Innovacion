<?php

/**
 * @file    PromocionesApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión de promociones escolares.
 * Delega la lógica de negocio en PromocionService y formatea
 * las respuestas con PromocionTransformer.
 *
 * Endpoints:
 *   GET    /api/promociones                → listar con filtros
 *   GET    /api/promociones/{id}           → detalle + estudiantes + sesiones
 *   POST   /api/promociones                → crear
 *   PUT    /api/promociones/{id}           → actualizar
 *   PATCH  /api/promociones/{id}/activar   → activar / desactivar
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Services\Promociones\PromocionService;
use App\Transformers\PromocionTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Promociones Escolares.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class PromocionesApi extends BaseApiController
{
    /** @var PromocionService Servicio con la lógica de negocio de promociones. */
    protected PromocionService $promocionService;

    /** @var PromocionTransformer Formateador de respuestas JSON. */
    protected PromocionTransformer $promocionTransformer;

    public function __construct()
    {
        $this->promocionService     = service('promocionService');
        $this->promocionTransformer = new PromocionTransformer();
    }

    /**
     * GET /api/promociones[?id_cotizacion=X&colegio=X&anio=X&activa=1]
     *
     * @return ResponseInterface 200 con la lista de promociones.
     */
    public function index()
    {
        $filters = array_filter([
            'id_cotizacion' => $this->request->getGet('id_cotizacion'),
            'colegio'       => $this->request->getGet('colegio'),
            'anio'          => $this->request->getGet('anio'),
            'activa'        => $this->request->getGet('activa'),
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

    /**
     * GET /api/promociones/{id}
     *
     * La respuesta incluye: colegio, cotización, lista de estudiantes y sesiones fotográficas.
     *
     * @param  mixed $id ID de la promoción.
     * @return ResponseInterface 200 con detalle | 404 si no existe.
     */
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

    /**
     * POST /api/promociones
     *
     * Body: { id_colegio, id_cotizacion, nombre, grado, num_estudiantes, seccion?, anio? }
     *
     * La cotización debe estar en estado APROBADA.
     *
     * @return ResponseInterface 201 con id_promocion | 404 | 409 | 422.
     */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'id_colegio'      => 'required|integer',
            'id_cotizacion'   => 'required|integer',
            'nombre'          => 'required|max_length[100]',
            'grado'           => 'required|in_list[Inicial,Jardin,Primaria,Secundaria,Superior,Otro]',
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
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Promoción creada', 'id_promocion' => $idPromocion]);
    }

    /**
     * PUT /api/promociones/{id}
     *
     * Body: { nombre?, grado?, seccion?, num_estudiantes? }
     *
     * @param  mixed $id ID de la promoción.
     * @return ResponseInterface 200 | 404 | 422.
     */
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
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Promoción actualizada']);
    }

    /**
     * PATCH /api/promociones/{id}/activar
     *
     * Body: { is_active: true | false }
     * Si is_active no se envía, invierte el estado actual (toggle).
     *
     * @param  mixed $id ID de la promoción.
     * @return ResponseInterface 200 con is_active | 404.
     */
    public function toggleActiva($id)
    {
        $body     = $this->request->getJSON(true) ?? [];
        $isActive = isset($body['is_active']) ? (bool) $body['is_active'] : null;

        try {
            $nuevo = $this->promocionService->toggleActiva((int) $id, $isActive);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'    => 'success',
                'message'   => 'Promoción ' . ($nuevo ? 'activada' : 'desactivada'),
                'is_active' => $nuevo,
            ]);
    }

}
