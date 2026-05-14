<?php

namespace App\Services\Clientes;

use App\Models\ClientesModel;
use App\Models\PersonasModel;

class ClienteService
{
    protected ClientesModel $clienteModel;
    protected PersonasModel $personaModel;

    public function __construct()
    {
        $this->clienteModel = new ClientesModel();
        $this->personaModel = new PersonasModel();
    }

    public function listar(): array
    {
        return $this->clienteModel->listarCompleto();
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->clienteModel->obtenerCompleto($id);
    }

    public function crear(array $data): int
    {
        $db = $this->personaModel->db;
        $db->transStart();

        $idPersona = $this->personaModel->insert([
            'nombres'          => $data['nombres'],
            'apellidos'        => $data['apellidos'] ?? null,
            'telefono'         => $data['telefono'],
            'correo'           => $data['correo'] ?? null,
            'tel_alternativo'  => $data['tel_alternativo'] ?? null,
            'numero_documento' => $data['numero_documento'],
            'tipo_documento'   => $data['tipo_documento'],
        ]);

        if ($idPersona === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->personaModel->errors()), 422);
        }

        $idCliente = $this->clienteModel->insert([
            'id_persona'          => $idPersona,
            'red_social'          => $data['red_social'] ?? null,
            'metodo_comunicacion' => $data['metodo_comunicacion'] ?? 'whatsapp',
            'acepta_promociones'  => $data['acepta_promociones'] ?? false,
            'estado'              => 'ACTIVO',
        ]);

        if ($idCliente === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->clienteModel->errors()), 422);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al crear el cliente', 500);
        }

        return $idCliente;
    }

    public function actualizar(int $id, array $data): void
    {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            throw new \RuntimeException('Cliente no encontrado', 404);
        }

        $db = $this->personaModel->db;
        $db->transStart();

        $personaData = array_filter([
            'nombres'          => $data['nombres'] ?? null,
            'apellidos'        => $data['apellidos'] ?? null,
            'telefono'         => $data['telefono'] ?? null,
            'correo'           => $data['correo'] ?? null,
            'tel_alternativo'  => $data['tel_alternativo'] ?? null,
            'numero_documento' => $data['numero_documento'] ?? null,
            'tipo_documento'   => $data['tipo_documento'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($personaData) && $this->personaModel->update($cliente['id_persona'], $personaData) === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->personaModel->errors()), 422);
        }

        $clienteData = array_filter([
            'red_social'          => $data['red_social'] ?? null,
            'metodo_comunicacion' => $data['metodo_comunicacion'] ?? null,
            'acepta_promociones'  => isset($data['acepta_promociones']) ? (bool) $data['acepta_promociones'] : null,
            'estado'              => $data['estado'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($clienteData) && $this->clienteModel->update($id, $clienteData) === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->clienteModel->errors()), 422);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al actualizar el cliente', 500);
        }
    }

    public function desactivar(int $id): void
    {
        if (!$this->clienteModel->find($id)) {
            throw new \RuntimeException('Cliente no encontrado', 404);
        }

        $this->clienteModel->update($id, ['estado' => 'INACTIVO']);
    }

    public function existeDocumento(string $numero, string $tipo, ?int $excludeClienteId = null): bool
    {
        return $this->clienteModel->existePorDocumento($numero, $tipo, $excludeClienteId);
    }
}
