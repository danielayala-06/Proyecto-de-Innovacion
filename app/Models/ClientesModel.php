<?php

/**
 * @file    ClientesModel.php
 * @package App\Models
 *
 * Modelo para la tabla `clientes`.
 * Extiende la entidad `personas` con datos propios del cliente:
 * red social, método de contacto preferido y estado de la relación comercial.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Clientes.
 *
 * Tabla: `clientes` (PK: id_cliente).
 * Relación: clientes.id_persona → personas.id_persona.
 */
class ClientesModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id_cliente';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_persona',
        'red_social',
        'metodo_comunicacion',
        'acepta_promociones',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_persona'          => 'required|is_natural_no_zero',
        'metodo_comunicacion' => 'required|in_list[whatsapp,llamada,correo,otro]',
        'acepta_promociones'  => 'permit_empty|in_list[0,1]',
        'estado'              => 'required|in_list[ACTIVO,INACTIVO]',
    ];
    protected $validationMessages = [
        'id_persona' => [
            'required' => 'La persona asociada es obligatoria.',
        ],
        'metodo_comunicacion' => [
            'required' => 'Debe seleccionar un método de comunicación.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista todos los clientes con los datos de persona incluidos (JOIN).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarCompleto(): array
    {
        return $this
            ->select('clientes.id_cliente, personas.nombres, personas.apellidos,
                      personas.telefono, personas.correo,
                      personas.numero_documento, personas.tipo_documento,
                      clientes.red_social, clientes.metodo_comunicacion,
                      clientes.acepta_promociones, clientes.estado')
            ->join('personas', 'personas.id_persona = clientes.id_persona')
            ->orderBy('clientes.id_cliente', 'DESC')
            ->findAll();
    }

    /**
     * Retorna el detalle completo de un cliente, incluyendo teléfono alternativo.
     *
     * @param  int                       $id ID del cliente.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerCompleto(int $id): ?array
    {
        return $this
            ->select('clientes.id_cliente, clientes.red_social,
                      clientes.metodo_comunicacion, clientes.acepta_promociones, clientes.estado,
                      personas.nombres, personas.apellidos, personas.telefono, personas.correo,
                      personas.tel_alternativo, personas.numero_documento, personas.tipo_documento')
            ->join('personas', 'personas.id_persona = clientes.id_persona')
            ->find($id) ?: null;
    }

    /**
     * Verifica si ya existe un cliente con el mismo número y tipo de documento.
     *
     * @param  string   $numero     Número de documento a buscar.
     * @param  string   $tipo       Tipo de documento (DNI, CE, PASAPORTE).
     * @param  int|null $excludeId  ID de cliente a excluir (para edición).
     * @return bool                 true si ya existe otro cliente con ese documento.
     */
    public function existePorDocumento(string $numero, string $tipo, ?int $excludeId = null): bool
    {
        $q = $this
            ->join('personas', 'personas.id_persona = clientes.id_persona')
            ->where('personas.numero_documento', $numero)
            ->where('personas.tipo_documento', $tipo);
        if ($excludeId !== null) {
            $q->where('clientes.id_cliente !=', $excludeId);
        }
        return (bool) $q->countAllResults();
    }
}
