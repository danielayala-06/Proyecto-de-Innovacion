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
     * CREAR CONTRATO
     */
    public function crear(array $data): array
    {
        $cotizacion = $this->cotizacionModel->find((int) $data['id_cotizacion']);

        if (!$cotizacion) {
            throw new \RuntimeException('Cotización no encontrada', 404);
        }

        if ($cotizacion['estado'] !== 'APROBADA') {
            throw new \RuntimeException(
                'La cotización debe estar APROBADA para generar un contrato', 409
            );
        }

        $existente = $this->contratoModel
            ->where('id_cotizacion', $data['id_cotizacion'])
            ->where('estado', 'ACTIVO')
            ->first();

        if ($existente) {
            throw new \RuntimeException('Ya existe un contrato activo para esta cotización', 409);
        }

        $adelanto = (float) $data['adelanto'];
        $total    = (float) $cotizacion['total_estimado'];

        if ($adelanto > $total) {
            throw new \RuntimeException(
                'El adelanto no puede superar el total de la cotización', 422
            );
        }

        $idContrato = $this->contratoModel->insert([
            'id_cotizacion'  => $data['id_cotizacion'],
            'fecha_creacion' => date('Y-m-d'),
            'fecha_emision'  => $data['fecha_emision'] ?? null,
            'adelanto'       => $adelanto,
            'total'          => $total,
            'observaciones'  => $data['observaciones'] ?? null,
            'estado'         => 'ACTIVO',
        ]);

        if ($idContrato === false) {
            throw new \RuntimeException(json_encode($this->contratoModel->errors()), 422);
        }

        return [
            'id_contrato' => $idContrato,
            'total'       => $total,
            'saldo'       => $total - $adelanto,
        ];
    }

    /**
     * ACTUALIZAR CONTRATO
     */
    public function actualizar(int $id, array $data): void
    {
        $contrato = $this->contratoModel->find($id);

        if (!$contrato) {
            throw new \RuntimeException('Contrato no encontrado', 404);
        }

        if ($contrato['estado'] !== 'ACTIVO') {
            throw new \RuntimeException('Solo se puede editar contratos ACTIVOS', 409);
        }

        $updateData = [];

        if (isset($data['adelanto'])) {
            $cot = $this->cotizacionModel->find((int) $contrato['id_cotizacion']);
            if ((float) $data['adelanto'] > (float) $cot['total_estimado']) {
                throw new \RuntimeException(
                    'El adelanto no puede superar el total de la cotización', 422
                );
            }
            $updateData['adelanto'] = $data['adelanto'];
        }

        if (array_key_exists('fecha_emision', $data)) $updateData['fecha_emision'] = $data['fecha_emision'];
        if (array_key_exists('observaciones',  $data)) $updateData['observaciones']  = $data['observaciones'];

        if (!empty($updateData) && $this->contratoModel->update($id, $updateData) === false) {
            throw new \RuntimeException(json_encode($this->contratoModel->errors()), 422);
        }
    }

    /**
     * CAMBIAR ESTADO DE CONTRATO
     */
    public function cambiarEstado(int $id, string $estado): void
    {
        if (!$this->contratoModel->find($id)) {
            throw new \RuntimeException('Contrato no encontrado', 404);
        }

        $updateData = ['estado' => $estado];

        if ($estado === 'COMPLETADO') {
            $updateData['fecha_emision'] = date('Y-m-d');
        }

        if ($this->contratoModel->update($id, $updateData) === false) {
            throw new \RuntimeException(json_encode($this->contratoModel->errors()), 422);
        }
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

        $pagosAdicionales = $this->db
            ->table('pagos pg')
            ->select('pg.fecha, pg.monto, fp.nombre_forma_pago')
            ->join('formas_pago fp', 'fp.id_form_pago = pg.id_form_pago', 'left')
            ->where('pg.id_contrato', $id)
            ->orderBy('pg.fecha', 'ASC')
            ->get()->getResultArray();

        $adelanto    = (float) $contrato['adelanto'];
        $total       = (float) $contrato['total'];
        $sumPagos    = array_sum(array_column($pagosAdicionales, 'monto'));
        $totalPagado = $adelanto + $sumPagos;

        $adelantoEntry = [
            'fecha'             => $contrato['fecha_creacion'],
            'monto'             => $adelanto,
            'nombre_forma_pago' => 'Adelanto inicial',
        ];

        $contrato['pagos']        = array_merge([$adelantoEntry], $pagosAdicionales);
        $contrato['total_pagado'] = round($totalPagado, 2);
        $contrato['saldo']        = round($total - $totalPagado, 2);

        return $contrato;
    }


}