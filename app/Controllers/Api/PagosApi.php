<?php

/**
 * @file    PagosApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para el registro y anulación de pagos.
 * Delega la lógica de negocio en PagoService y formatea
 * las respuestas con PagoTransformer.
 *
 * Endpoints:
 *   GET    /api/pagos?contrato={id}  → listar pagos (todos o filtrados por contrato)
 *   GET    /api/pagos/{id}           → detalle de un pago
 *   POST   /api/pagos                → registrar nuevo pago
 *   DELETE /api/pagos/{id}           → anular pago
 *   GET    /api/formas-pago          → catálogo de formas de pago
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Services\Pagos\PagoService;
use App\Transformers\PagoTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Pagos.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class PagosApi extends BaseApiController
{
    /** @var PagoService Servicio con la lógica de negocio de pagos. */
    protected PagoService $pagoService;

    /** @var PagoTransformer Formateador de respuestas JSON. */
    protected PagoTransformer $pagoTransformer;

    public function __construct()
    {
        $this->pagoService     = new PagoService();
        $this->pagoTransformer = new PagoTransformer();
    }

    /**
     * GET /api/pagos[?contrato={id}]
     *
     * @return ResponseInterface 200 con la lista de pagos.
     */
    public function index()
    {
        $idContrato = $this->request->getGet('contrato');

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->pagoTransformer->transformMany(
                    $this->pagoService->listar($idContrato ? (int) $idContrato : null)
                ),
            ]);
    }

    /**
     * GET /api/pagos/{id}
     *
     * @param  mixed $id ID del pago.
     * @return ResponseInterface 200 con detalle | 404 si no existe.
     */
    public function show($id)
    {
        $pago = $this->pagoService->obtenerPorId((int) $id);

        if (!$pago) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Pago no encontrado']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->pagoTransformer->transform($pago),
            ]);
    }

    /**
     * POST /api/pagos
     *
     * Body: { id_contrato, id_form_pago, monto, moneda?, voucher?, fecha? }
     *
     * Cuando el monto excede el saldo, el 409 incluye el campo `saldo` para informar al cliente.
     *
     * @return ResponseInterface 201 con id_pago, total_pagado, saldo, completado | 409 | 422.
     */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'id_contrato'  => 'required|integer',
            'id_form_pago' => 'required|integer',
            'monto'        => 'required|decimal|greater_than[0]',
            'moneda'       => 'permit_empty|in_list[PEN,USD,EUR]',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        try {
            $result = $this->pagoService->registrar($body);
        } catch (\RuntimeException $e) {
            $extra = ($e->getCode() === 409) ? json_decode($e->getMessage(), true) : null;
            if (is_array($extra) && isset($extra['message'])) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                    ->setJSON(array_merge(['status' => 'error'], $extra));
            }
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(array_merge(['status' => 'success', 'message' => 'Pago registrado'], $result));
    }

    /**
     * DELETE /api/pagos/{id}
     *
     * Anula un pago. Solo se puede anular pagos de contratos ACTIVOS.
     *
     * @param  mixed $id ID del pago.
     * @return ResponseInterface 200 | 404 | 409.
     */
    public function delete($id)
    {
        try {
            $this->pagoService->anular((int) $id);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Pago anulado']);
    }

    /**
     * GET /api/formas-pago
     *
     * @return ResponseInterface 200 con la lista de formas de pago ordenadas alfabéticamente.
     */
    public function formasPago()
    {
        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->pagoService->formasPago()]);
    }

}
