<?php

namespace App\Models;

use CodeIgniter\Model;

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
        'id_form_pago',
        'monto',
        'moneda',
        'voucher',
        'fecha',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'id_contrato' => 'required|is_natural_no_zero',
        'id_form_pago' => 'required|is_natural_no_zero',
        'monto' => 'required|decimal|greater_than[0]',
        'moneda' => 'required|in_list[PEN,USD,EUR]',
        'fecha' => 'required|valid_date',
    ];
    protected $validationMessages = [
        'monto' => [
            'greater_than' => 'El monto debe ser mayor a cero.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function listarConDetalles(?int $idContrato = null): array
    {
        $q = $this
            ->select('pagos.id_pago, pagos.fecha, pagos.monto, pagos.moneda,
                      pagos.voucher, pagos.id_contrato,
                      formas_pago.nombre_forma_pago, formas_pago.tipo_pago,
                      CONCAT(personas.nombres," ",COALESCE(personas.apellidos,"")) AS cliente')
            ->join('formas_pago',  'formas_pago.id_form_pago = pagos.id_form_pago')
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

    public function obtenerConDetalle(int $id): ?array
    {
        return $this
            ->select('pagos.*, formas_pago.nombre_forma_pago, formas_pago.tipo_pago,
                      contratos.total, contratos.estado AS estado_contrato,
                      CONCAT(personas.nombres," ",COALESCE(personas.apellidos,"")) AS cliente')
            ->join('formas_pago',  'formas_pago.id_form_pago = pagos.id_form_pago')
            ->join('contratos',    'contratos.id_contrato = pagos.id_contrato')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',     'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',     'personas.id_persona = clientes.id_persona')
            ->find($id) ?: null;
    }

    public function sumarPorContrato(int $idContrato): float
    {
        $r = $this->selectSum('monto', 'total_pagado')->where('id_contrato', $idContrato)->first();
        return (float) ($r['total_pagado'] ?? 0);
    }

    public function historialPorContrato(int $idContrato): array
    {
        return $this
            ->select('pagos.fecha, pagos.monto, formas_pago.nombre_forma_pago')
            ->join('formas_pago', 'formas_pago.id_form_pago = pagos.id_form_pago', 'left')
            ->where('pagos.id_contrato', $idContrato)
            ->orderBy('pagos.fecha', 'ASC')
            ->findAll();
    }
}
