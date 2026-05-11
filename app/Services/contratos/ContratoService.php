<?php

namespace App\Services\Contratos;

use App\Models\ContratosModel;
use App\Models\CotizacionesModel;
use App\Models\PagosModel;
use Config\Database;

class ContratoService
{
    protected ContratosModel $contratoModel;
    protected CotizacionesModel $cotizacionModel;
    protected PagosModel $pagoModel;
    protected $db;

    public function __construct()
    {
        $this->contratoModel   = new ContratosModel();
        $this->cotizacionModel = new CotizacionesModel();
        $this->pagoModel       = new PagosModel();

        $this->db = Database::connect();
    }

    /**
     * LISTAR CONTRATOS
     */
    public function listar(
        array $filters = []
    ): array {

        $builder =
            $this->contratoModel
                ->select([
                    'contratos.id_contrato',
                    'contratos.id_cotizacion',
                    'contratos.fecha_creacion',
                    'contratos.fecha_emision',
                    'contratos.adelanto',
                    'contratos.total',
                    'contratos.estado',
                    'contratos.observaciones',
                    "CONCAT(
                        personas.nombres,
                        ' ',
                        COALESCE(personas.apellidos, '')
                    ) AS cliente",
                    'personas.telefono',
                    'cotizaciones.total_estimado',
                    'cotizaciones.estado AS estado_cotizacion'
                ])
                ->join(
                    'cotizaciones',
                    'cotizaciones.id_cotizacion = contratos.id_cotizacion'
                )
                ->join(
                    'clientes',
                    'clientes.id_cliente = cotizaciones.id_cliente'
                )
                ->join(
                    'personas',
                    'personas.id_persona = clientes.id_persona'
                );

        /**
         * FILTRO ESTADO
         */
        if (!empty($filters['estado'])) {
            $builder->where(
                'contratos.estado',
                strtoupper($filters['estado'])
            );
        }
        return $builder->orderBy(
                'contratos.fecha_creacion',
                'DESC'
            )
            ->findAll();
    }

    /**
     * OBTENER CONTRATO COMPLETO CON PAGOS
     */
    public function buscarPorID(int $id): ?array
    {
        $contrato =
            $this->contratoModel
                ->select([
                    'contratos.id_contrato',
                    'contratos.id_cotizacion',
                    'contratos.fecha_creacion',
                    'contratos.fecha_emision',
                    'contratos.adelanto',
                    'contratos.total',
                    'contratos.estado',
                    'contratos.observaciones',
                    "CONCAT(
                        personas.nombres,
                        ' ',
                        COALESCE(personas.apellidos, '')
                    ) AS cliente",
                    'personas.telefono',
                    'cotizaciones.total_estimado',
                ])
                ->join(
                    'cotizaciones',
                    'cotizaciones.id_cotizacion = contratos.id_cotizacion'
                )
                ->join(
                    'clientes',
                    'clientes.id_cliente = cotizaciones.id_cliente'
                )
                ->join(
                    'personas',
                    'personas.id_persona = clientes.id_persona'
                )
                ->where('contratos.id_contrato', $id)
                ->first();
        if (!$contrato) return null;

        $pagos = $this->db
            ->table('pagos pg')
            ->select('pg.fecha, pg.monto, fp.nombre_forma_pago')
            ->join('formas_pago fp', 'fp.id_form_pago = pg.id_form_pago')
            ->where('pg.id_contrato', $id)
            ->orderBy('pg.fecha', 'ASC')
            ->get()->getResultArray();

        $sumPagos    = array_sum(array_column($pagos, 'monto'));
        $adelanto    = (float) $contrato['adelanto'];
        $total       = (float) $contrato['total'];
        $totalPagado = $adelanto + $sumPagos;

        $contrato['pagos']        = $pagos;
        $contrato['total_pagado'] = round($totalPagado, 2);
        $contrato['saldo']        = round($total - $totalPagado, 2);

        return $contrato;
    }


}