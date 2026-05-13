<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\Paquetes\PaqueteService;
use App\Transformers\PaqueteTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PaquetesApi
 * Base URL: /api/paquetes
 *
 * GET    /api/paquetes                        → listar con filtros opcionales
 * GET    /api/paquetes/{id}                   → detalle + productos + reglas
 * POST   /api/paquetes                        → crear paquete
 * PUT    /api/paquetes/{id}                   → actualizar datos del paquete
 * PATCH  /api/paquetes/{id}/estado            → activar / desactivar
 * POST   /api/paquetes/{id}/productos         → agregar producto al paquete
 * DELETE /api/paquetes/{id}/productos/{pid}   → quitar producto del paquete
 */
class PaquetesApi extends BaseController
{
    protected PaqueteService    $paqueteService;
    protected PaqueteTransformer $paqueteTransformer;

    public function __construct()
    {
        $this->paqueteService     = new PaqueteService();
        $this->paqueteTransformer = new PaqueteTransformer();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/paquetes[?nivel=secundaria&estado=ACTIVO]
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $filters = array_filter([
            'nivel'  => $this->request->getGet('nivel'),
            'estado' => $this->request->getGet('estado'),
        ], fn($v) => $v !== null);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->paqueteTransformer->transformMany(
                    $this->paqueteService->listar($filters)
                ),
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/paquetes/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $paquete = $this->paqueteService->obtenerPorId((int) $id);

        if (!$paquete) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Paquete no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->paqueteTransformer->transform($paquete)]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/paquetes
    // Body: { nombre_paquete, nivel_disponible, precio, descripcion?,
    //         categoria?, imagen?, productos?: [{ id_producto, cantidad }] }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'nombre_paquete'   => 'required|max_length[150]',
            'nivel_disponible' => 'required|in_list[inicial-primaria,secundaria,postgrado,otro]',
            'precio'           => 'required|decimal',
            'categoria'        => 'permit_empty|in_list[Cuadros,Anuarios,Paquetes,otros]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $idPaquete = $this->paqueteService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Paquete creado', 'id_paquete' => $idPaquete]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PUT /api/paquetes/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'nombre_paquete'   => 'permit_empty|max_length[150]',
            'nivel_disponible' => 'permit_empty|in_list[inicial-primaria,secundaria,postgrado,otro]',
            'precio'           => 'permit_empty|decimal',
            'categoria'        => 'permit_empty|in_list[Cuadros,Anuarios,Paquetes,otros]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $this->paqueteService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Paquete actualizado']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/paquetes/{id}/estado
    // Body: { estado: "ACTIVO" | "INACTIVO" }
    // ────────────────────────────────────────────────────────────────────────
    public function cambiarEstado($id)
    {
        $body   = $this->request->getJSON(true) ?? [];
        $estado = strtoupper($body['estado'] ?? '');

        if (!in_array($estado, ['ACTIVO', 'INACTIVO'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ACTIVO o INACTIVO']);
        }

        try {
            $this->paqueteService->cambiarEstado((int) $id, $estado);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Paquete {$estado}"]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/paquetes/{id}/productos
    // Body: { id_producto, cantidad? }
    // ────────────────────────────────────────────────────────────────────────
    public function agregarProducto($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        if (empty($body['id_producto'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'id_producto es requerido']);
        }

        try {
            $action = $this->paqueteService->agregarProducto((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode($action === 'created' ? ResponseInterface::HTTP_CREATED : ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'  => 'success',
                'message' => $action === 'created'
                    ? 'Producto agregado al paquete'
                    : 'Cantidad de producto actualizada',
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // DELETE /api/paquetes/{id}/productos/{pid}
    // ────────────────────────────────────────────────────────────────────────
    public function quitarProducto($id, $pid)
    {
        try {
            $this->paqueteService->quitarProducto((int) $id, (int) $pid);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Producto removido del paquete']);
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
