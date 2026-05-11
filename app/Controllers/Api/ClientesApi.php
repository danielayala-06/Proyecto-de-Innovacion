<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ClientesApi
 * Base URL: /api/clientes
 *
 * GET    /api/clientes              → listar todos
 * GET    /api/clientes/{id}         → obtener uno
 * POST   /api/clientes              → crear (persona + cliente)
 * PUT    /api/clientes/{id}         → actualizar
 * DELETE /api/clientes/{id}         → eliminar (soft delete)
 */
class ClientesApi extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/clientes
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $clientes = $this->db->table('clientes c')
            ->select('c.id_cliente, p.nombres, p.apellidos, p.telefono, p.correo,
                      p.numero_documento, p.tipo_documento, c.red_social,
                      c.metodo_comunicacion, c.acepta_promociones, c.estado')
            ->join('personas p', 'p.id_persona = c.id_persona')
            ->orderBy('c.id_cliente', 'DESC')
            ->get()->getResultArray();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $clientes]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/clientes/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $cliente = $this->db->table('clientes c')
            ->select('c.*, p.nombres, p.apellidos, p.telefono, p.correo,
                      p.tel_alternativo, p.numero_documento, p.tipo_documento')
            ->join('personas p', 'p.id_persona = c.id_persona')
            ->where('c.id_cliente', $id)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cliente no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $cliente]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/clientes
    // Body JSON: { nombres, apellidos, telefono, correo, numero_documento,
    //              tipo_documento, red_social, metodo_comunicacion, acepta_promociones }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'nombres'          => 'required|max_length[100]',
            'apellidos'        => 'permit_empty|max_length[100]',
            'telefono'         => 'required|exact_length[9]',
            'correo'           => 'permit_empty|valid_email|max_length[150]',
            'numero_documento' => 'required|max_length[50]',
            'tipo_documento'   => 'required|in_list[DNI,CE,PASAPORTE]',
            'metodo_comunicacion' => 'permit_empty|in_list[correo,whatsapp,llamada,otro]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $this->db->transStart();

        // Insertar persona
        $this->db->table('personas')->insert([
            'nombres'          => $body['nombres'],
            'apellidos'        => $body['apellidos'] ?? null,
            'telefono'         => $body['telefono'],
            'correo'           => $body['correo'] ?? null,
            'tel_alternativo'  => $body['tel_alternativo'] ?? null,
            'numero_documento' => $body['numero_documento'],
            'tipo_documento'   => $body['tipo_documento'],
        ]);
        $idPersona = $this->db->insertID();

        // Insertar cliente
        $this->db->table('clientes')->insert([
            'id_persona'          => $idPersona,
            'red_social'          => $body['red_social'] ?? null,
            'metodo_comunicacion' => $body['metodo_comunicacion'] ?? 'whatsapp',
            'acepta_promociones'  => $body['acepta_promociones'] ?? false,
            'estado'              => 'ACTIVO',
        ]);
        $idCliente = $this->db->insertID();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['status' => 'error', 'message' => 'Error al crear el cliente']);
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
        $cliente = $this->db->table('clientes')->where('id_cliente', $id)->get()->getRowArray();

        if (!$cliente) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cliente no encontrado']);
        }

        $body = $this->request->getJSON(true);

        $this->db->transStart();

        // Actualizar persona
        $personaData = array_filter([
            'nombres'          => $body['nombres'] ?? null,
            'apellidos'        => $body['apellidos'] ?? null,
            'telefono'         => $body['telefono'] ?? null,
            'correo'           => $body['correo'] ?? null,
            'tel_alternativo'  => $body['tel_alternativo'] ?? null,
            'numero_documento' => $body['numero_documento'] ?? null,
            'tipo_documento'   => $body['tipo_documento'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($personaData)) {
            $this->db->table('personas')
                ->where('id_persona', $cliente['id_persona'])
                ->update($personaData);
        }

        // Actualizar cliente
        $clienteData = array_filter([
            'red_social'          => $body['red_social'] ?? null,
            'metodo_comunicacion' => $body['metodo_comunicacion'] ?? null,
            'acepta_promociones'  => $body['acepta_promociones'] ?? null,
            'estado'              => $body['estado'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($clienteData)) {
            $this->db->table('clientes')->where('id_cliente', $id)->update($clienteData);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['status' => 'error', 'message' => 'Error al actualizar el cliente']);
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
        $cliente = $this->db->table('clientes')->where('id_cliente', $id)->get()->getRowArray();

        if (!$cliente) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cliente no encontrado']);
        }

        $this->db->table('clientes')
            ->where('id_cliente', $id)
            ->update(['estado' => 'INACTIVO']);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cliente desactivado']);
    }
}
