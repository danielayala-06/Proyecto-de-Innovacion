<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\Pagos\PagoService;
use App\Transformers\PagoTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PagosApi
 * Base URL: /api/pagos
 *
 * GET    /api/pagos?contrato={id}   → listar pagos de un contrato
 * GET    /api/pagos/{id}            → detalle de un pago
 * POST   /api/pagos                 → registrar nuevo pago
 * DELETE /api/pagos/{id}            → anular pago
 * GET    /api/formas-pago           → listar formas de pago
 */
class PagosApi extends BaseController
{
    protected PagoService    $pagoService;
    protected PagoTransformer $pagoTransformer;

    public function __construct()
    {
        $this->pagoService     = new PagoService();
        $this->pagoTransformer = new PagoTransformer();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/pagos?contrato={id}
    // ────────────────────────────────────────────────────────────────────────
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

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/pagos/{id}
    // ────────────────────────────────────────────────────────────────────────
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

    // ────────────────────────────────────────────────────────────────────────
    // POST /api/pagos
    // Body: { id_contrato, id_form_pago, monto, moneda?, voucher?, fecha? }
    // ────────────────────────────────────────────────────────────────────────
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
            // Caso especial: saldo excedido devuelve JSON con 'saldo' adicional
            $extra = ($e->getCode() === 409) ? json_decode($e->getMessage(), true) : null;
            if (is_array($extra) && isset($extra['message'])) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                    ->setJSON(array_merge(['status' => 'error'], $extra));
            }
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON(array_merge(['status' => 'success', 'message' => 'Pago registrado'], $result));
    }

    // ────────────────────────────────────────────────────────────────────────
    // DELETE /api/pagos/{id}  → anulación
    // ────────────────────────────────────────────────────────────────────────
    public function delete($id)
    {
        try {
            $this->pagoService->anular((int) $id);
        } catch (\RuntimeException $e) {
            return $this->_serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Pago anulado']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/formas-pago
    // ────────────────────────────────────────────────────────────────────────
    public function formasPago()
    {
        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->pagoService->formasPago()]);
    }

    private function _serviceError(\RuntimeException $e): \CodeIgniter\HTTP\ResponseInterface
    {
        $code   = (int) $e->getCode() ?: 500;
        $errors = ($code === 422) ? json_decode($e->getMessage(), true) : null;

        $httpStatus = match ($code) {
            404     => ResponseInterface::HTTP_NOT_FOUND,
            409     => ResponseInterface::HTTP_CONFLICT,
            422     => ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
            default => ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
        };

        return $this->response
            ->setStatusCode($httpStatus)
            ->setJSON(is_array($errors)
                ? ['status' => 'error', 'errors'  => $errors]
                : ['status' => 'error', 'message' => $e->getMessage()]
            );
    }
}
