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
                    'contratos.fecha_creacion',
                    'contratos.fecha_emision',
                    'contratos.adelanto',
                    'contratos.total',
                    'contratos.estado',
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
     * OBTENER CONTRATO COMPLETO
     */
    public function buscarPorID(int $id): ?array
    {
        $contrato =
            $this->contratoModel
                ->select([
                    'contratos.id_contrato',
                    'contratos.fecha_creacion',
                    'contratos.fecha_emision',
                    'contratos.adelanto',
                    'contratos.total',
                    'contratos.estado',
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
                ->where('contratos.estado', 'ACTIVO')
                ->first();
        if(!$contrato) return null;

        return $contrato ?? null;
    }


}