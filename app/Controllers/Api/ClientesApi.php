<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\Clientes\ClienteService;
use App\Transformers\ClienteTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ClientesApi
 * Base URL: /api/clientes
 *
 * GET    /api/clientes              → listar todos
 * GET    /api/clientes/{id}         → obtener uno
 * POST   /api/clientes              → crear (persona + cliente)
 * PUT    /api/clientes/{id}         → actualizar
 * DELETE /api/clientes/{id}         → desactivar
 */
class ClientesApi extends BaseController
{
    protected ClienteService     $clienteService;
    protected ClienteTransformer $clienteTransformer;

    public function __construct()
    {
        $this->clienteService     = new ClienteService();
        $this->clienteTransformer = new ClienteTransformer();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/clientes
    // ────────────────────────────────────────────────────────────────────────
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

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/clientes/{id}
    // ────────────────────────────────────────────────────────────────────────
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

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/clientes
    // Body: { nombres, apellidos?, telefono, correo?, numero_documento,
    //         tipo_documento, red_social?, metodo_comunicacion?, acepta_promociones? }
    // ────────────────────────────────────────────────────────────────────────
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
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(['status' => 'success', 'message' => 'Cliente creado', 'id_cliente' => $idCliente]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PUT /api/clientes/{id}
    // ────────────────────────────────────────────────────────────────────────
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
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cliente actualizado']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // DELETE /api/clientes/{id}  → soft delete
    // ────────────────────────────────────────────────────────────────────────
    public function delete($id)
    {
        try {
            $this->clienteService->desactivar((int) $id);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cliente desactivado']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Convierte RuntimeException del servicio en respuesta JSON.
    // Código 422 con JSON → devuelve 'errors' (errores del modelo).
    // Otros códigos → devuelve 'message'.
    // ────────────────────────────────────────────────────────────────────────
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
                ? ['status' => 'error', 'errors'   => $errors]
                : ['status' => 'error', 'message'  => $e->getMessage()]
            );
    }
}
