<?php

/**
 * @file    ContratosModel.php
 * @package App\Models
 *
 * Modelo para la tabla `contratos`.
 * Un contrato formaliza una cotización APROBADA, registra el adelanto inicial
 * y controla el ciclo de vida de los pagos (ACTIVO → COMPLETADO | CANCELADO).
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Contratos.
 *
 * Tabla: `contratos` (PK: id_contrato).
 * Relación: contratos.id_cotizacion → cotizaciones.id_cotizacion.
 * Estados válidos: ACTIVO | COMPLETADO | CANCELADO.
 */
class ContratosModel extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id_contrato';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cotizacion',
        'fecha_emision',
        'fecha_creacion',
        'adelanto',
        'total',
        'observaciones',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_cotizacion' => 'required|is_natural_no_zero',
        'adelanto'      => 'required|decimal|greater_than_equal_to[0]',
        'total'         => 'required|decimal|greater_than_equal_to[0]',
        'estado'        => 'required|in_list[ACTIVO,CANCELADO,COMPLETADO]',
    ];
    protected $validationMessages = [
        'id_cotizacion' => [
            'required' => 'La cotización asociada es obligatoria.',
        ],
        'adelanto' => [
            'required'              => 'El adelanto es obligatorio.',
            'greater_than_equal_to' => 'El adelanto no puede ser negativo.',
        ],
        'total' => [
            'required' => 'El total del contrato es obligatorio.',
        ],
        'estado' => [
            'in_list' => 'Estado inválido. Use: ACTIVO, CANCELADO o COMPLETADO.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista todos los contratos con datos del cliente y de la cotización vinculada.
     *
     * Filtros admitidos: estado (ACTIVO | COMPLETADO | CANCELADO).
     *
     * @param  array<string, mixed>     $filters
     * @return array<int, array<string, mixed>>
     */
    public function listarCompleto(array $filters = []): array
    {
        $q = $this
            ->select([
                'contratos.id_contrato', 'contratos.id_cotizacion',
                'contratos.fecha_creacion', 'contratos.fecha_emision',
                'contratos.adelanto', 'contratos.total',
                'contratos.estado', 'contratos.observaciones',
                "CONCAT(personas.nombres,' ',COALESCE(personas.apellidos,'')) AS cliente",
                'personas.telefono',
                'cotizaciones.total_estimado',
                'cotizaciones.estado AS estado_cotizacion',
            ])
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',    'personas.id_persona = clientes.id_persona')
            ->orderBy('contratos.id_contrato', 'DESC');

        if (!empty($filters['estado'])) {
            $q->where('contratos.estado', strtoupper($filters['estado']));
        }

        return $q->orderBy('contratos.fecha_creacion', 'DESC')->findAll();
    }

    /**
     * Retorna el detalle de un contrato con los datos del cliente y la cotización.
     *
     * @param  int                       $id ID del contrato.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerConCliente(int $id): ?array
    {
        return $this
            ->select([
                'contratos.id_contrato', 'contratos.id_cotizacion',
                'contratos.fecha_creacion', 'contratos.fecha_emision',
                'contratos.adelanto', 'contratos.total',
                'contratos.estado', 'contratos.observaciones',
                "CONCAT(personas.nombres,' ',COALESCE(personas.apellidos,'')) AS cliente",
                'personas.telefono', 'cotizaciones.total_estimado',
            ])
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',    'personas.id_persona = clientes.id_persona')
            ->where('contratos.id_contrato', $id)
            ->first();
    }
}
