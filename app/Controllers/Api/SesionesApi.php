<?php

/**
 * @file    SesionesApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión de sesiones fotográficas y su asistencia.
 * Delega la lógica de negocio en SesionService y formatea
 * las respuestas con SesionTransformer.
 *
 * Endpoints:
 *   GET    /api/sesiones[?id_promocion=X&id_contrato=X&estado=X&tipo=X]
 *   GET    /api/sesiones/{id}
 *   POST   /api/sesiones
 *   PUT    /api/sesiones/{id}
 *   PATCH  /api/sesiones/{id}/estado
 *   GET    /api/sesiones/{id}/limite?tipo=X
 *   POST   /api/sesiones/{id}/asistencia              → agregar estudiante
 *   DELETE /api/sesiones/{id}/asistencia/{eid}        → quitar estudiante
 *   PATCH  /api/sesiones/{id}/asistencia/{eid}        → marcar asistencia
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Services\Sesiones\SesionService;
use App\Transformers\SesionTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Sesiones Fotográficas.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class SesionesApi extends BaseApiController
{
    /** @var SesionService Servicio con la lógica de negocio de sesiones. */
    protected SesionService $sesionService;

    /** @var SesionTransformer Formateador de respuestas JSON. */
    protected SesionTransformer $sesionTransformer;

    public function __construct()
    {
        $this->sesionService     = new SesionService();
        $this->sesionTransformer = new SesionTransformer();
    }

    /**
     * GET /api/sesiones[?id_promocion=X&id_contrato=X&estado=X&tipo=X]
     *
     * @return ResponseInterface 200 con la lista de sesiones.
     */
    public function index()
    {
        $filters = array_filter([
            'id_promocion' => $this->request->getGet('id_promocion'),
            'id_contrato'  => $this->request->getGet('id_contrato'),
            'estado'       => $this->request->getGet('estado'),
            'tipo'         => $this->request->getGet('tipo'),
        ], fn($v) => $v !== null && $v !== '');

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->sesionTransformer->transformMany($this->sesionService->listar($filters)),
            ]);
    }

    /**
     * GET /api/sesiones/{id}
     *
     * Incluye la lista de asistencia con el estado de cada estudiante.
     *
     * @param  mixed $id ID de la sesión.
     * @return ResponseInterface 200 con detalle | 404 si no existe.
     */
    public function show($id)
    {
        $sesion = $this->sesionService->obtenerPorId((int) $id);

        if (!$sesion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Sesión no encontrada']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->sesionTransformer->transform($sesion)]);
    }

    /**
     * POST /api/sesiones
     *
     * Body: { id_promocion, fecha_hora_sesion (YYYY-MM-DD HH:MM:SS), tipo, observaciones? }
     *
     * @return ResponseInterface 201 con id_sesion | 409 si se superó el límite | 422 si falla validación.
     */
    public function create()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $rules = [
            'id_promocion'      => 'required|integer|is_natural_no_zero',
            'fecha_hora_sesion' => 'required|valid_date[Y-m-d H:i:s]',
            'tipo'              => 'required|in_list[exteriores,colegio,estudio,otro]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $id = $this->sesionService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Sesión creada', 'id_sesion' => $id]);
    }

    /**
     * PUT /api/sesiones/{id}
     *
     * Body: { fecha_hora_sesion?, observaciones? }
     *
     * No permite editar sesiones en estado 'finalizado'.
     *
     * @param  mixed $id ID de la sesión.
     * @return ResponseInterface 200 | 404 | 409 | 422.
     */
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        try {
            $this->sesionService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Sesión actualizada']);
    }

    /**
     * PATCH /api/sesiones/{id}/estado
     *
     * Body: { estado: "pendiente" | "finalizado" | "cancelado" }
     *
     * @param  mixed $id ID de la sesión.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function cambiarEstado($id)
    {
        $body   = $this->request->getJSON(true) ?? [];
        $estado = strtolower($body['estado'] ?? '');

        if (!in_array($estado, ['pendiente', 'finalizado', 'cancelado'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: pendiente, finalizado o cancelado']);
        }

        try {
            $this->sesionService->cambiarEstado((int) $id, $estado);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Sesión marcada como {$estado}"]);
    }

    /**
     * GET /api/sesiones/{id}/limite?tipo=exteriores
     *
     * Devuelve cuántas sesiones del tipo dado están permitidas y cuántas ya se usaron.
     *
     * @param  mixed $id ID de la promoción (no de la sesión).
     * @return ResponseInterface 200 con { tipo, permitidas, usadas, puede_crear } | 404 | 422.
     */
    public function limite($id)
    {
        $tipo = $this->request->getGet('tipo') ?? '';

        if (!in_array($tipo, ['exteriores', 'colegio', 'estudio', 'otro'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Tipo inválido. Use: exteriores, colegio, estudio u otro']);
        }

        try {
            $data = $this->sesionService->calcularLimite((int) $id, $tipo);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $data]);
    }

    /**
     * POST /api/sesiones/{id}/asistencia
     *
     * Body: { id_estudiante }
     *
     * Valida que el estudiante pertenezca a la misma promoción de la sesión.
     *
     * @param  mixed $id ID de la sesión.
     * @return ResponseInterface 201 | 404 | 409 | 422.
     */
    public function agregarEstudiante($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        if (empty($body['id_estudiante'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_estudiante es requerido']);
        }

        try {
            $this->sesionService->agregarEstudiante((int) $id, (int) $body['id_estudiante']);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante agregado a la sesión']);
    }

    /**
     * DELETE /api/sesiones/{id}/asistencia/{eid}
     *
     * @param  mixed $id  ID de la sesión.
     * @param  mixed $eid ID del estudiante a quitar.
     * @return ResponseInterface 200 | 404.
     */
    public function quitarEstudiante($id, $eid)
    {
        try {
            $this->sesionService->quitarEstudiante((int) $id, (int) $eid);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante removido de la sesión']);
    }

    /**
     * PATCH /api/sesiones/{id}/asistencia/{eid}
     *
     * Body: { asistio: 1 | 0 | null }
     *
     * @param  mixed $id  ID de la sesión.
     * @param  mixed $eid ID del estudiante.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function marcarAsistencia($id, $eid)
    {
        $body    = $this->request->getJSON(true) ?? [];
        $asistio = array_key_exists('asistio', $body) ? $body['asistio'] : 'MISSING';

        if ($asistio === 'MISSING' || !in_array($asistio, [0, 1, null], true)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'asistio debe ser 1, 0 o null']);
        }

        try {
            $this->sesionService->marcarAsistencia((int) $id, (int) $eid, $asistio);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Asistencia actualizada']);
    }

}
