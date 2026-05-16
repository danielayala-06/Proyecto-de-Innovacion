<?php

/**
 * @file    ClientesApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión de clientes.
 * Delega toda la lógica de negocio en ClienteService y formatea
 * las respuestas con ClienteTransformer.
 *
 * Endpoints:
 *   GET    /api/clientes          → listar todos
 *   GET    /api/clientes/{id}     → obtener detalle
 *   POST   /api/clientes          → crear (persona + cliente)
 *   PUT    /api/clientes/{id}     → actualizar
 *   DELETE /api/clientes/{id}     → desactivar (soft delete)
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Services\Clientes\ClienteService;
use App\Transformers\ClienteTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Clientes.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class ClientesApi extends BaseApiController
{
    /** @var ClienteService Servicio con la lógica de negocio de clientes. */
    protected ClienteService $clienteService;

    /** @var ClienteTransformer Formateador de respuestas JSON. */
    protected ClienteTransformer $clienteTransformer;

    public function __construct()
    {
        $this->clienteService     = new ClienteService();
        $this->clienteTransformer = new ClienteTransformer();
    }

    /**
     * GET /api/clientes
     *
     * @return ResponseInterface 200 con la lista de clientes.
     */
    public function index()
    {
        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->clienteTransformer->transformMany(
                    $this->clienteService->listar()
                ),
            ]);
    }

    /**
     * GET /api/clientes/{id}
     *
     * @param  mixed $id ID del cliente.
     * @return ResponseInterface 200 con el detalle | 404 si no existe.
     */
    public function show($id)
    {
        $cliente = $this->clienteService->obtenerPorId((int) $id);

        if (!$cliente) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cliente no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->clienteTransformer->transform($cliente),
            ]);
    }

    /**
     * POST /api/clientes
     *
     * Body: { nombres, apellidos?, telefono, correo?, numero_documento,
     *         tipo_documento, red_social?, metodo_comunicacion?, acepta_promociones? }
     *
     * @return ResponseInterface 201 con id_cliente | 409 si ya existe el documento | 422 si falla validación.
     */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'nombres'             => 'required|max_length[100]',
            'apellidos'           => 'permit_empty|max_length[100]',
            'telefono'            => 'required|exact_length[9]',
            'correo'              => 'permit_empty|valid_email|max_length[150]',
            'numero_documento'    => 'required|max_length[50]',
            'tipo_documento'      => 'required|in_list[DNI,CE,PASAPORTE]',
            'metodo_comunicacion' => 'permit_empty|in_list[correo,whatsapp,llamada,otro]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        if ($this->clienteService->existeDocumento($body['numero_documento'], $body['tipo_documento'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'Ya existe un cliente con ese número de documento']);
        }

        try {
            $idCliente = $this->clienteService->crear($body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Cliente creado', 'id_cliente' => $idCliente]);
    }

    /**
     * PUT /api/clientes/{id}
     *
     * Body: { telefono?, correo?, tipo_documento?, metodo_comunicacion?, estado? }
     *
     * @param  mixed $id ID del cliente.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'telefono'            => 'permit_empty|exact_length[9]',
            'correo'              => 'permit_empty|valid_email|max_length[150]',
            'tipo_documento'      => 'permit_empty|in_list[DNI,CE,PASAPORTE]',
            'metodo_comunicacion' => 'permit_empty|in_list[correo,whatsapp,llamada,otro]',
            'estado'              => 'permit_empty|in_list[ACTIVO,INACTIVO]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $this->clienteService->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cliente actualizado']);
    }

    /**
     * DELETE /api/clientes/{id}
     *
     * Desactiva el cliente sin eliminarlo físicamente.
     *
     * @param  mixed $id ID del cliente.
     * @return ResponseInterface 200 | 404.
     */
    public function delete($id)
    {
        try {
            $this->clienteService->desactivar((int) $id);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cliente desactivado']);
    }

}
