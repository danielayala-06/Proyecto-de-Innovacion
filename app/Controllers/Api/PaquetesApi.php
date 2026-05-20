<?php

/**
 * @file    PaquetesApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión del catálogo de paquetes fotográficos.
 * Delega la lógica de negocio en PaqueteService y formatea
 * las respuestas con PaqueteTransformer.
 *
 * Endpoints:
 *   GET    /api/paquetes                        → listar con filtros opcionales
 *   GET    /api/paquetes/{id}                   → detalle + productos + reglas
 *   POST   /api/paquetes                        → crear paquete
 *   PUT    /api/paquetes/{id}                   → actualizar datos
 *   PATCH  /api/paquetes/{id}/estado            → activar / desactivar
 *   POST   /api/paquetes/{id}/productos         → agregar producto al paquete
 *   DELETE /api/paquetes/{id}/productos/{pid}   → quitar producto del paquete
 *   POST   /api/paquetes/{id}/reglas            → crear regla de bonificación
 *   DELETE /api/paquetes/reglas/{rid}           → eliminar regla
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Models\ProductosModel;
use App\Services\Paquetes\PaqueteService;
use App\Transformers\PaqueteTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Paquetes.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class PaquetesApi extends BaseApiController
{
    /** @var PaqueteService Servicio con la lógica de negocio de paquetes. */
    protected PaqueteService $paqueteService;

    /** @var PaqueteTransformer Formateador de respuestas JSON. */
    protected PaqueteTransformer $paqueteTransformer;

    /** @var ProductosModel Para el endpoint GET /api/productos. */
    protected ProductosModel $productosModel;

    public function __construct()
    {
        $this->paqueteService     = new PaqueteService();
        $this->paqueteTransformer = new PaqueteTransformer();
        $this->productosModel     = new ProductosModel();
    }

    /**
     * GET /api/paquetes[?nivel=secundaria&estado=ACTIVO]
     *
     * @return ResponseInterface 200 con la lista de paquetes.
     */
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

    /**
     * GET /api/paquetes/{id}
     *
     * Incluye productos asociados y reglas de bonificación.
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 200 con detalle | 404 si no existe.
     */
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

    /**
     * POST /api/paquetes
     *
     * Body: { nombre_paquete, nivel_disponible, precio, descripcion?,
     *         categoria?, imagen?, productos?: [{ id_producto, cantidad }] }
     *
     * @return ResponseInterface 201 con id_paquete | 422 si falla validación.
     */
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
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Paquete creado', 'id_paquete' => $idPaquete]);
    }

    /**
     * PUT /api/paquetes/{id}
     *
     * Body: { nombre_paquete?, nivel_disponible?, precio?, descripcion?, categoria? }
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 200 | 404 | 422.
     */
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
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Paquete actualizado']);
    }

    /**
     * PATCH /api/paquetes/{id}/estado
     *
     * Body: { estado: "ACTIVO" | "INACTIVO" }
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 200 | 404 | 422.
     */
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
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Paquete {$estado}"]);
    }

    /**
     * POST /api/paquetes/{id}/productos
     *
     * Body: { id_producto, cantidad? }
     *
     * Si el producto ya está en el paquete, actualiza solo la cantidad.
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 201 (creado) | 200 (cantidad actualizada) | 404 | 422.
     */
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
            return $this->serviceError($e);
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

    /**
     * DELETE /api/paquetes/{id}/productos/{pid}
     *
     * @param  mixed $id  ID del paquete.
     * @param  mixed $pid ID del registro en paquetes_productos.
     * @return ResponseInterface 200 | 404.
     */
    public function quitarProducto($id, $pid)
    {
        try {
            $this->paqueteService->quitarProducto((int) $id, (int) $pid);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Producto removido del paquete']);
    }

    /**
     * POST /api/paquetes/{id}/reglas
     *
     * Body: { tipo_condicion, valor_condicion, tipo_beneficio, valor_beneficio, descripcion }
     *
     * @param  mixed $id ID del paquete.
     * @return ResponseInterface 201 con id_regla | 404 | 422.
     */
    public function crearRegla($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'tipo_condicion'  => 'required|in_list[CANTIDAD_MIN,CANTIDAD_MAX]',
            'valor_condicion' => 'required|decimal',
            'tipo_beneficio'  => 'required|in_list[producto_gratis,sesion_unica,otro]',
            'descripcion'     => 'required|max_length[300]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $idRegla = $this->paqueteService->crearRegla((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Regla creada', 'id_regla' => $idRegla]);
    }

    /**
     * GET /api/productos[?estado=ACTIVO]
     *
     * Lista simplificada de productos para selectores en formularios.
     *
     * @return ResponseInterface 200 con la lista de productos.
     */
    public function indexProductos()
    {
        $productos = $this->productosModel->where('estado', 'ACTIVO')->orderBy('nombre_producto', 'ASC')->findAll();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => array_map(fn($p) => [
                    'id_producto'    => (int) $p['id_producto'],
                    'nombre_producto' => $p['nombre_producto'],
                    'categoria'      => $p['categoria'],
                ], $productos),
            ]);
    }

    /**
     * DELETE /api/paquetes/reglas/{rid}
     *
     * @param  mixed $rid ID de la regla.
     * @return ResponseInterface 200 | 404.
     */
    public function eliminarRegla($rid)
    {
        try {
            $this->paqueteService->eliminarRegla((int) $rid);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Regla eliminada']);
    }

}
