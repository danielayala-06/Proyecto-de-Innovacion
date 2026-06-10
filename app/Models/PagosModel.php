<?php

/**
 * @file    PagosModel.php
 * @package App\Models
 *
 * Modelo para la tabla `pagos`.
 * Registra los abonos sobre un contrato. La forma de pago se almacena
 * como texto libre (VARCHAR) en `forma_pago`.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Pagos.
 *
 * Tabla: `pagos` (PK: id_pago).
 * Relaciones: id_contrato → contratos.
 * Monedas admitidas: PEN | USD | EUR.
 */
class PagosModel extends Model
{
    protected $table            = 'pagos';
    protected $primaryKey       = 'id_pago';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_contrato',
        'forma_pago',
        'monto',
        'moneda',
        'voucher',
        'fecha',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_contrato' => 'required|is_natural_no_zero',
        'forma_pago'  => 'required|max_length[60]|regex_match[/^[\p{L}\s\/\-\.]+$/u]',
        'monto'       => 'required|decimal|greater_than[0]',
        'moneda'      => 'required|in_list[PEN,USD,EUR]',
        'fecha'       => 'required|valid_date',
    ];
    protected $validationMessages = [
        'monto'      => ['greater_than' => 'El monto debe ser mayor a cero.'],
        'forma_pago' => [
            'required'    => 'La forma de pago es obligatoria.',
            'regex_match' => 'La forma de pago solo puede contener letras.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista pagos con datos del cliente y contrato.
     *
     * @param  int|null                 $idContrato Filtrar por contrato (null = todos).
     * @return array<int, array<string, mixed>>
     */
    public function listarConDetalles(?int $idContrato = null): array
    {
        $q = $this
            ->select('pagos.id_pago, pagos.fecha, pagos.monto, pagos.moneda,
                      pagos.voucher, pagos.id_contrato, pagos.forma_pago,
                      CONCAT(personas.nombres," ",COALESCE(personas.apellidos,"")) AS cliente')
            ->join('contratos',    'contratos.id_contrato = pagos.id_contrato')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',     'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',     'personas.id_persona = clientes.id_persona')
            ->orderBy('pagos.fecha', 'DESC');

        if ($idContrato !== null) {
            $q->where('pagos.id_contrato', $idContrato);
        }

        return $q->findAll();
    }

    /**
     * Retorna el detalle de un pago con datos del contrato.
     *
     * @param  int                       $id ID del pago.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerConDetalle(int $id): ?array
    {
        return $this
            ->select('pagos.*, contratos.total, contratos.estado AS estado_contrato,
                      CONCAT(personas.nombres," ",COALESCE(personas.apellidos,"")) AS cliente')
            ->join('contratos',    'contratos.id_contrato = pagos.id_contrato')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',     'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',     'personas.id_persona = clientes.id_persona')
            ->find($id) ?: null;
    }

    /**
     * Suma todos los montos de pagos registrados para un contrato.
     *
     * @param  int   $idContrato ID del contrato.
     * @return float             Total acumulado de pagos.
     */
    public function sumarPorContrato(int $idContrato): float
    {
        $r = $this->selectSum('monto', 'total_pagado')->where('id_contrato', $idContrato)->first();
        return (float) ($r['total_pagado'] ?? 0);
    }

    /**
     * Retorna el historial de pagos de un contrato para mostrar en el detalle.
     *
     * @param  int                      $idContrato ID del contrato.
     * @return array<int, array<string, mixed>>
     */
    public function historialPorContrato(int $idContrato): array
    {
        return $this
            ->select('pagos.id_pago, pagos.fecha, pagos.monto, pagos.forma_pago')
            ->where('pagos.id_contrato', $idContrato)
            ->orderBy('pagos.id_pago', 'ASC')
            ->findAll();
    }

    /**
     * Retorna el pago de adelanto de un contrato (el primero registrado por id).
     */
    public function adelantoPorContrato(int $idContrato): ?array
    {
        return $this->where('id_contrato', $idContrato)->orderBy('id_pago', 'ASC')->first() ?: null;
    }
}
