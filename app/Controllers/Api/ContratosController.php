<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ContratosController
 * Base URL: /api/contratos
 *
 * GET    /api/contratos              → listar con filtros
 * GET    /api/contratos/{id}         → detalle + pagos asociados
 * POST   /api/contratos              → crear (requiere cotización APROBADA)
 * PATCH  /api/contratos/{id}/estado  → cambiar estado
 */
class ContratosController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/contratos[?estado=ACTIVO]
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $builder = $this->db->table('contratos cn')
            ->select('cn.id_contrato, cn.id_cotizacion, cn.fecha_creacion, cn.fecha_emision,
                      cn.adelanto, cn.total, cn.estado,
                      CONCAT(p.nombres, " ", COALESCE(p.apellidos,"")) AS cliente,
                      p.telefono,
                      ct.total_estimado, ct.estado AS estado_cotizacion')
            ->join('cotizaciones ct', 'ct.id_cotizacion = cn.id_cotizacion')
            ->join('clientes c',      'c.id_cliente = ct.id_cliente')
            ->join('personas p',      'p.id_persona = c.id_persona')
            ->orderBy('cn.fecha_creacion', 'DESC');

        if ($estado = $this->request->getGet('estado')) {
            $builder->where('cn.estado', strtoupper($estado));
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $builder->get()->getResultArray()]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/contratos/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $contrato = $this->db->table('contratos cn')
            ->select('cn.*, CONCAT(p.nombres, " ", COALESCE(p.apellidos,"")) AS cliente,
                      p.telefono, p.correo, c.id_cliente,
                      ct.total_estimado, ct.observaciones AS obs_cotizacion')
            ->join('cotizaciones ct', 'ct.id_cotizacion = cn.id_cotizacion')
            ->join('clientes c',      'c.id_cliente = ct.id_cliente')
            ->join('personas p',      'p.id_persona = c.id_persona')
            ->where('cn.id_contrato', $id)
            ->get()->getRowArray();

        if (!$contrato) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Contrato no encontrado']);
        }

        // Pagos adicionales registrados
        $pagosAdicionales = $this->db->table('pagos pg')
            ->select('pg.id_pago, pg.fecha, pg.monto, pg.moneda, pg.voucher, fp.nombre_forma_pago')
            ->join('formas_pago fp', 'fp.id_form_pago = pg.id_form_pago')
            ->where('pg.id_contrato', $id)
            ->orderBy('pg.fecha', 'ASC')
            ->get()->getResultArray();

        // El adelanto inicial se muestra como primer pago en el historial
        $adelantoEntry = [
            'id_pago'           => null,
            'fecha'             => $contrato['fecha_emision'] ?? $contrato['fecha_creacion'],
            'monto'             => $contrato['adelanto'],
            'moneda'            => 'PEN',
            'voucher'           => null,
            'nombre_forma_pago' => 'Adelanto inicial',
        ];

        $totalPagado = (float)$contrato['adelanto'] + array_sum(array_column($pagosAdicionales, 'monto'));
        $saldo       = (float)$contrato['total'] - $totalPagado;

        $contrato['pagos']        = array_merge([$adelantoEntry], $pagosAdicionales);
        $contrato['total_pagado'] = $totalPagado;
        $contrato['saldo']        = round($saldo, 2);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $contrato]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/contratos
    // Body: { id_cotizacion, adelanto, observaciones }
    // Requiere que la cotización esté APROBADA
    // ────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $rules = [
            'id_cotizacion' => 'required|integer',
            'adelanto'      => 'required|decimal',
        ];

        $body = $this->request->getJSON(true) ?? [];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        // Verificar cotización
        $cotizacion = $this->db->table('cotizaciones')
            ->where('id_cotizacion', $body['id_cotizacion'])
            ->get()->getRowArray();

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        if ($cotizacion['estado'] !== 'APROBADA') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'La cotización debe estar APROBADA para generar un contrato']);
        }

        // Verificar que no exista ya un contrato ACTIVO para esa cotización
        $contratoExistente = $this->db->table('contratos')
            ->where('id_cotizacion', $body['id_cotizacion'])
            ->where('estado', 'ACTIVO')
            ->get()->getRowArray();

        if ($contratoExistente) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'Ya existe un contrato activo para esta cotización']);
        }

        if ((float)$body['adelanto'] > (float)$cotizacion['total_estimado']) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'El adelanto no puede superar el total de la cotización']);
        }

        $this->db->table('contratos')->insert([
            'id_cotizacion' => $body['id_cotizacion'],
            'fecha_creacion'=> date('Y-m-d'),
            'fecha_emision' => $body['fecha_emision'] ?? null,
            'adelanto'      => $body['adelanto'],
            'total'         => $cotizacion['total_estimado'],
            'observaciones' => $body['observaciones'] ?? null,
            'estado'        => 'ACTIVO',
        ]);

        $idContrato = $this->db->insertID();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status'      => 'success',
                'message'     => 'Contrato creado',
                'id_contrato' => $idContrato,
                'total'       => $cotizacion['total_estimado'],
                'saldo'       => $cotizacion['total_estimado'] - $body['adelanto'],
            ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/contratos/{id}
    // Body: { adelanto?, fecha_emision?, observaciones? }
    // ────────────────────────────────────────────────────────────────────────
    public function update($id)
    {
        $body = $this->request->getJSON(true) ?? [];

        $contrato = $this->db->table('contratos')->where('id_contrato', $id)->get()->getRowArray();
        if (!$contrato) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Contrato no encontrado']);
        }

        $updateData = [];
        if (array_key_exists('adelanto', $body))
            $updateData['adelanto']     = max(0, (float)$body['adelanto']);
        if (array_key_exists('fecha_emision', $body))
            $updateData['fecha_emision'] = $body['fecha_emision'] ?: null;
        if (array_key_exists('observaciones', $body))
            $updateData['observaciones'] = $body['observaciones'] ?: null;

        if (!empty($updateData)) {
            $this->db->table('contratos')->where('id_contrato', $id)->update($updateData);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Contrato actualizado']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PATCH /api/contratos/{id}/estado
    // Body: { estado: "ACTIVO" | "CANCELADO" | "COMPLETADO" }
    // ────────────────────────────────────────────────────────────────────────
    public function cambiarEstado($id)
    {
        $body   = $this->request->getJSON(true);
        $estado = strtoupper($body['estado'] ?? '');
        $validos = ['ACTIVO', 'CANCELADO', 'COMPLETADO'];

        if (!in_array($estado, $validos)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ' . implode(', ', $validos)]);
        }

        $contrato = $this->db->table('contratos')->where('id_contrato', $id)->get()->getRowArray();

        if (!$contrato) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Contrato no encontrado']);
        }

        $updateData = ['estado' => $estado];
        if ($estado === 'COMPLETADO') {
            $updateData['fecha_emision'] = date('Y-m-d');
        }

        $this->db->table('contratos')->where('id_contrato', $id)->update($updateData);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Estado del contrato cambiado a {$estado}"]);
    }
}
