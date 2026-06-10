<?php

/**
 * @file    ClienteService.php
 * @package App\Services\Clientes
 *
 * Capa de negocio para la gestión de clientes.
 * Encapsula la lógica de creación y actualización de clientes,
 * coordinando la transacción entre las tablas `personas` y `clientes`.
 */

namespace App\Services\Clientes;

use App\Models\ClientesModel;
use App\Models\PersonasModel;

/**
 * Servicio de Clientes.
 *
 * Responsabilidades:
 * - Listar y consultar clientes con sus datos de persona.
 * - Crear un cliente en transacción (persona → cliente).
 * - Actualizar datos personales y comerciales en transacción.
 * - Desactivar un cliente (soft-delete de negocio).
 * - Verificar unicidad de documento de identidad.
 */
class ClienteService
{
    /** @var ClientesModel Acceso a la tabla `clientes`. */
    protected ClientesModel $clienteModel;

    /** @var PersonasModel Acceso a la tabla `personas`. */
    protected PersonasModel $personaModel;

    public function __construct()
    {
        $this->clienteModel = new ClientesModel();
        $this->personaModel = new PersonasModel();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna todos los clientes con sus datos de persona fusionados.
     *
     * @return array<int, array<string, mixed>> Lista de clientes.
     */
    public function listar(): array
    {
        return $this->clienteModel->listarCompleto();
    }

    /**
     * Retorna un cliente con sus datos de persona, o null si no existe.
     *
     * @param  int        $id ID del cliente.
     * @return array<string, mixed>|null
     */
    public function obtenerPorId(int $id): ?array
    {
        return $this->clienteModel->obtenerCompleto($id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea un cliente en una sola transacción: persona → cliente.
     *
     * Campos esperados en $data:
     * - nombres, apellidos?, telefono, correo?, tel_alternativo?
     * - numero_documento, tipo_documento
     * - red_social?, metodo_comunicacion?, acepta_promociones?
     *
     * @param  array<string, mixed> $data Datos del formulario.
     * @return int                        ID del cliente creado.
     *
     * @throws \RuntimeException 422 si falla la validación del modelo.
     * @throws \RuntimeException 500 si la transacción no se confirma.
     */
    public function crear(array $data): int
    {
        $db = $this->personaModel->db;
        $db->transStart();

        $idPersona = $this->personaModel->insert([
            'nombres'          => $data['nombres'],
            'apellidos'        => $data['apellidos']        ?? null,
            'telefono'         => $data['telefono'],
            'correo'           => $data['correo']           ?? null,
            'tel_alternativo'  => $data['tel_alternativo']  ?? null,
            'numero_documento' => !empty($data['numero_documento']) ? $data['numero_documento'] : null,
            'tipo_documento'   => !empty($data['tipo_documento'])   ? $data['tipo_documento']   : null,
        ]);

        if ($idPersona === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->personaModel->errors()), 422);
        }

        $idCliente = $this->clienteModel->insert([
            'id_persona'          => $idPersona,
            'red_social'          => $data['red_social']          ?? null,
            'metodo_comunicacion' => $data['metodo_comunicacion'] ?? 'whatsapp',
            'acepta_promociones'  => $data['acepta_promociones']  ?? false,
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

    /**
     * Actualiza datos personales y/o comerciales de un cliente existente.
     *
     * Solo actualiza los campos presentes en $data (parcial). La operación
     * se ejecuta en una transacción para garantizar consistencia entre
     * `personas` y `clientes`.
     *
     * @param  int                  $id   ID del cliente.
     * @param  array<string, mixed> $data Campos a actualizar (parcial).
     * @return void
     *
     * @throws \RuntimeException 404 si el cliente no existe.
     * @throws \RuntimeException 422 si falla la validación del modelo.
     * @throws \RuntimeException 500 si la transacción no se confirma.
     */
    public function actualizar(int $id, array $data): void
    {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            throw new \RuntimeException('Cliente no encontrado', 404);
        }

        $db = $this->personaModel->db;
        $db->transStart();

        $personaData = array_filter([
            'nombres'          => $data['nombres']          ?? null,
            'apellidos'        => $data['apellidos']        ?? null,
            'telefono'         => $data['telefono']         ?? null,
            'correo'           => $data['correo']           ?? null,
            'tel_alternativo'  => $data['tel_alternativo']  ?? null,
            'numero_documento' => $data['numero_documento'] ?? null,
            'tipo_documento'   => $data['tipo_documento']   ?? null,
        ], fn($v) => $v !== null);

        if (!empty($personaData) && $this->personaModel->update($cliente['id_persona'], $personaData) === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->personaModel->errors()), 422);
        }

        $clienteData = array_filter([
            'red_social'          => $data['red_social']          ?? null,
            'metodo_comunicacion' => $data['metodo_comunicacion'] ?? null,
            'acepta_promociones'  => isset($data['acepta_promociones'])
                                        ? (bool) $data['acepta_promociones']
                                        : null,
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

    /**
     * Desactiva un cliente cambiando su estado a INACTIVO (no elimina el registro).
     *
     * @param  int  $id ID del cliente.
     * @return void
     *
     * @throws \RuntimeException 404 si el cliente no existe.
     */
    public function desactivar(int $id): void
    {
        if (!$this->clienteModel->find($id)) {
            throw new \RuntimeException('Cliente no encontrado', 404);
        }

        $this->clienteModel->update($id, ['estado' => 'INACTIVO']);
    }

    /**
     * Verifica si ya existe un documento de identidad registrado,
     * opcionalmente excluyendo al cliente que se está editando.
     *
     * @param  string   $numero           Número de documento.
     * @param  string   $tipo             Tipo de documento (DNI, CE, etc.).
     * @param  int|null $excludeClienteId ID a excluir de la búsqueda.
     * @return bool                       true si el documento ya existe.
     */
    public function existeDocumento(string $numero, string $tipo, ?int $excludeClienteId = null): bool
    {
        return $this->clienteModel->existePorDocumento($numero, $tipo, $excludeClienteId);
    }
}
