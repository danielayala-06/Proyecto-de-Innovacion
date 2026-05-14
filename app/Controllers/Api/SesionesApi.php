<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\Sesiones\SesionService;
use App\Transformers\SesionTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SesionesApi
 * Base URL: /api/sesiones
 *
 * GET    /api/sesiones[?id_promocion=X&id_contrato=X&estado=X]
 * GET    /api/sesiones/{id}
 * POST   /api/sesiones
 * PUT    /api/sesiones/{id}
 * PATCH  /api/sesiones/{id}/estado
 * GET    /api/sesiones/{id}/limite?tipo=X
 * GET    /api/sesiones/{id}/asistencia
 * POST   /api/sesiones/{id}/asistencia                     → agregar estudiante
 * DELETE /api/sesiones/{id}/asistencia/{id_estudiante}     → quitar estudiante
 * PATCH  /api/sesiones/{id}/asistencia/{id_estudiante}     → marcar asistencia
 */
class SesionesApi extends BaseController
{
    protected SesionService     $sesionService;
    protected SesionTransformer $sesionTransformer;

    public function __construct()
    {
        $this->sesionService     = new SesionService();
        $this->sesionTransformer = new SesionTransformer();
    }

    // GET /api/sesiones
    public function index()
    {
        $filters = array_filter([
            'id_promocion' => $this->request->getGet('id_promocion'),
            'id_contrato'  => $this->request->getGet('id_contrato'),
            'estado'       => $this->request->getGet('estado'),
            'tipo'         => $this->request->getGet('tipo'),
        ], fn($v) => $v !== null && $v !== '');

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setJSON([
            'status' => 'success',
            'data'   => $this->sesionTransformer->transformMany($this->sesionService->listar($filters)),
        ]);
    }

    // GET /api/sesiones/{id}
    public function show($id)
    {
        $sesion = $this->sesionService->obtenerPorId((int) $id);
        if (!$sesion) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Sesión no encontrada']);
        }
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->sesionTransformer->transform($sesion)]);
    }

    // POST /api/sesiones
    // Body: { id_promocion, fecha_hora_sesion, tipo, observaciones? }
    public function create()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $rules = [
            'id_promocion'      => 'required|integer|is_natural_no_zero',
            'fecha_hora_sesion' => 'required|valid_date[Y-m-d H:i:s]',
            'tipo'              => 'required|in_list[exteriores,colegio,estudio,otro]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $id = $this->sesionService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Sesión creada', 'id_sesion' => $id]);
    }

    // PUT /api/sesiones/{id}
    // Body: { fecha_hora_sesion?, observaciones? }
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];
        try {
            $this->sesionService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Sesión actualizada']);
    }

    // PATCH /api/sesiones/{id}/estado
    // Body: { estado: pendiente|finalizado|cancelado }
    public function cambiarEstado($id)
    {
        $body   = $this->request->getJSON(true) ?? [];
        $estado = strtolower($body['estado'] ?? '');

        if (!in_array($estado, ['pendiente', 'finalizado', 'cancelado'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: pendiente, finalizado o cancelado']);
        }

        try {
            $this->sesionService->cambiarEstado((int) $id, $estado);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Sesión marcada como {$estado}"]);
    }

    // GET /api/sesiones/{id}/limite?tipo=exteriores
    public function limite($id)
    {
        $tipo = $this->request->getGet('tipo') ?? '';
        if (!in_array($tipo, ['exteriores', 'colegio', 'estudio', 'otro'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Tipo inválido. Use: exteriores, colegio, estudio u otro']);
        }

        try {
            $data = $this->sesionService->calcularLimite((int) $id, $tipo);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $data]);
    }

    // POST /api/sesiones/{id}/asistencia
    // Body: { id_estudiante }
    public function agregarEstudiante($id)
    {
        $body = $this->request->getJSON(true) ?? [];
        if (empty($body['id_estudiante'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_estudiante es requerido']);
        }

        try {
            $this->sesionService->agregarEstudiante((int) $id, (int) $body['id_estudiante']);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante agregado a la sesión']);
    }

    // DELETE /api/sesiones/{id}/asistencia/{eid}
    public function quitarEstudiante($id, $eid)
    {
        try {
            $this->sesionService->quitarEstudiante((int) $id, (int) $eid);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Estudiante removido de la sesión']);
    }

    // PATCH /api/sesiones/{id}/asistencia/{eid}
    // Body: { asistio: 1|0|null }
    public function marcarAsistencia($id, $eid)
    {
        $body    = $this->request->getJSON(true) ?? [];
        $asistio = array_key_exists('asistio', $body) ? $body['asistio'] : 'MISSING';

        if ($asistio === 'MISSING' || !in_array($asistio, [0, 1, null], true)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'asistio debe ser 1, 0 o null']);
        }

        try {
            $this->sesionService->marcarAsistencia((int) $id, (int) $eid, $asistio);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Asistencia actualizada']);
    }

    private function _serviceError(\RuntimeException $e): ResponseInterface
    {
        $code   = (int) $e->getCode() ?: 500;
        $errors = ($code === 422) ? json_decode($e->getMessage(), true) : null;

        $httpStatus = match ($code) {
            404     => ResponseInterface::HTTP_NOT_FOUND,
            409     => ResponseInterface::HTTP_CONFLICT,
            422     => ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
            default => ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
        };

        return $this->response->setStatusCode($httpStatus)->setJSON(
            is_array($errors)
                ? ['status' => 'error', 'errors'  => $errors]
                : ['status' => 'error', 'message' => $e->getMessage()]
        );
    }
}
