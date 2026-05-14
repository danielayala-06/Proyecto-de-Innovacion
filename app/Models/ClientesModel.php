<?php

namespace App\Models;

use CodeIgniter\Model;

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

    // Validation
    protected $validationRules = [
        'id_persona' => 'required|is_natural_no_zero',
        'metodo_comunicacion' => 'required|in_list[whatsapp,llamada,correo,otro]',
        'acepta_promociones' => 'permit_empty|in_list[0,1]',
        'estado' => 'required|in_list[ACTIVO,INACTIVO]'
    ];
    protected $validationMessages = [
        'id_persona' => [
            'required' => 'La persona asociada es obligatoria.'
        ],
        'metodo_comunicacion' => [
            'required' => 'Debe seleccionar un método de comunicación.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

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
