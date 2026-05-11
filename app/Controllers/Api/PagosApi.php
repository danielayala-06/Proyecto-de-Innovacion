<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PagosApi
 * Base URL: /api/pagos
 *
 * GET    /api/pagos?contrato={id}   → listar pagos de un contrato
 * GET    /api/pagos/{id}            → detalle de un pago
 * POST   /api/pagos                 → registrar nuevo pago
 * DELETE /api/pagos/{id}            → anular pago
 */
class PagosApi extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/pagos?contrato={id}
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $idContrato = $this->request->getGet('contrato');

        $builder = $this->db->table('pagos pg')
            ->select('pg.id_pago, pg.fecha, pg.monto, pg.moneda, pg.voucher,
                      fp.nombre_forma_pago, fp.tipo_pago,
                      pg.id_contrato,
                      CONCAT(p.nombres, " ", COALESCE(p.apellidos,"")) AS cliente')
            ->join('formas_pago fp', 'fp.id_form_pago = pg.id_form_pago')
            ->join('contratos cn',   'cn.id_contrato = pg.id_contrato')
            ->join('cotizaciones ct','ct.id_cotizacion = cn.id_cotizacion')
            ->join('clientes c',     'c.id_cliente = ct.id_cliente')
            ->join('personas p',     'p.id_persona = c.id_persona')
            ->orderBy('pg.fecha', 'DESC');

        if ($idContrato) {
            $builder->where('pg.id_contrato', $idContrato);
        }

        $pagos = $builder->get()->getResultArray();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $pagos]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/pagos/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $pago = $this->db->table('pagos pg')
            ->select('pg.*, fp.nombre_forma_pago, fp.tipo_pago,
                      cn.total, cn.estado AS estado_contrato,
                      CONCAT(p.nombres, " ", COALESCE(p.apellidos,"")) AS cliente')
            ->join('formas_pago fp', 'fp.id_form_pago = pg.id_form_pago')
            ->join('contratos cn',   'cn.id_contrato = pg.id_contrato')
            ->join('cotizaciones ct','ct.id_cotizacion = cn.id_cotizacion')
            ->join('clientes c',     'c.id_cliente = ct.id_cliente')
            ->join('personas p',     'p.id_persona = c.id_persona')
            ->where('pg.id_pago', $id)
            ->get()->getRowArray();

        if (!$pago) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Pago no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $pago]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/pagos
    // Body: { id_contrato, id_form_pago, monto, moneda, voucher, fecha }
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $rules = [
            'id_contrato'  => 'required|integer',
            'id_form_pago' => 'required|integer',
            'monto'        => 'required|decimal',
            'moneda'       => 'permit_empty|in_list[PEN,USD,EUR]',
        ];

        if (!$this->validate($rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $body = $this->request->getJSON(true);

        // Verificar contrato activo
        $contrato = $this->db->table('contratos')
            ->where('id_contrato', $body['id_contrato'])
            ->get()->getRowArray();

        if (!$contrato) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Contrato no encontrado']);
        }

        if ($contrato['estado'] !== 'ACTIVO') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'Solo se pueden registrar pagos en contratos ACTIVOS']);
        }

        // Calcular saldo disponible
        $totalPagado = (float) $this->db->table('pagos')
            ->selectSum('monto')
            ->where('id_contrato', $body['id_contrato'])
            ->get()->getRow()->monto;

        $saldo = (float)$contrato['total'] - $totalPagado;

        if ((float)$body['monto'] > $saldo) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON([
                    'status'  => 'error',
                    'message' => "El monto excede el saldo pendiente",
                    'saldo'   => round($saldo, 2),
                ]);
        }

        $this->db->table('pagos')->insert([
            'id_contrato'  => $body['id_contrato'],
            'id_form_pago' => $body['id_form_pago'],
            'monto'        => $body['monto'],
            'moneda'       => $body['moneda'] ?? 'PEN',
            'voucher'      => $body['voucher'] ?? null,
            'fecha'        => $body['fecha'] ?? date('Y-m-d'),
        ]);

        $idPago       = $this->db->insertID();
        $nuevoTotalPagado = $totalPagado + (float)$body['monto'];
        $nuevoSaldo   = (float)$contrato['total'] - $nuevoTotalPagado;

        // Marcar contrato como COMPLETADO si el saldo llega a 0
        if ($nuevoSaldo <= 0) {
            $this->db->table('contratos')
                ->where('id_contrato', $body['id_contrato'])
                ->update(['estado' => 'COMPLETADO']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status'       => 'success',
                'message'      => 'Pago registrado',
                'id_pago'      => $idPago,
                'total_pagado' => round($nuevoTotalPagado, 2),
                'saldo'        => round($nuevoSaldo, 2),
                'completado'   => $nuevoSaldo <= 0,
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // DELETE /api/pagos/{id}  → anulación
    // ────────────────────────────────────────────────────────────────────────
    public function delete($id)
    {
        $pago = $this->db->table('pagos')->where('id_pago', $id)->get()->getRowArray();

        if (!$pago) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Pago no encontrado']);
        }

        // Solo se puede anular si el contrato sigue ACTIVO
        $contrato = $this->db->table('contratos')
            ->where('id_contrato', $pago['id_contrato'])
            ->get()->getRowArray();

        if ($contrato['estado'] !== 'ACTIVO') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'No se puede anular pagos de contratos no activos']);
        }

        $this->db->table('pagos')->where('id_pago', $id)->delete();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Pago anulado']);
    }
}
