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

        $body = $this->request->getJSON(true) ?? [];

        $validation = \Config\Services::validation();
        if (!$validation->setRules($rules)->run($body)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $validation->getErrors()]);
        }

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

        // Calcular saldo disponible (total - adelanto - pagos ya registrados)
        $sumPagos = (float) $this->db->table('pagos')
            ->selectSum('monto')
            ->where('id_contrato', $body['id_contrato'])
            ->get()->getRow()->monto;

        $saldo = (float)$contrato['total'] - (float)$contrato['adelanto'] - $sumPagos;

        if ((float)$body['monto'] > $saldo + 0.001) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'El monto excede el saldo pendiente',
                    'saldo'   => round($saldo, 2),
                ]);
        }

        // Validar fecha de pago
        $fechaStr  = $body['fecha'] ?? date('Y-m-d');
        $fechaPago = \DateTime::createFromFormat('Y-m-d', $fechaStr);
        if (!$fechaPago) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Formato de fecha inválido. Use YYYY-MM-DD.']);
        }
        $hoy      = new \DateTime('today');
        $minFecha = (clone $hoy)->modify('-3 days');
        $fechaPago->setTime(0, 0, 0);
        if ($fechaPago > $hoy) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'La fecha de pago no puede ser en el futuro.']);
        }
        if ($fechaPago < $minFecha) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'La fecha de pago no puede ser anterior a 3 días de hoy.']);
        }

        $this->db->table('pagos')->insert([
            'id_contrato'  => $body['id_contrato'],
            'id_form_pago' => $body['id_form_pago'],
            'monto'        => $body['monto'],
            'moneda'       => $body['moneda'] ?? 'PEN',
            'voucher'      => $body['voucher'] ?? null,
            'fecha'        => $fechaStr,
        ]);

        $idPago           = $this->db->insertID();
        $nuevoTotalPagado = (float)$contrato['adelanto'] + $sumPagos + (float)$body['monto'];
        $nuevoSaldo       = (float)$contrato['total'] - $nuevoTotalPagado;

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

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/formas-pago
    // ────────────────────────────────────────────────────────────────────────
    public function formasPago()
    {
        $formas = $this->db->table('formas_pago')
            ->select('id_form_pago, nombre_forma_pago, tipo_pago')
            ->orderBy('nombre_forma_pago', 'ASC')
            ->get()->getResultArray();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $formas]);
    }
}
