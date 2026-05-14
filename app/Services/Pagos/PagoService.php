<?php

namespace App\Services\Pagos;

use App\Models\PagosModel;
use App\Models\ContratosModel;
use App\Models\FormasPagoModel;

class PagoService
{
    protected PagosModel      $pagoModel;
    protected ContratosModel  $contratoModel;
    protected FormasPagoModel $formasPagoModel;

    public function __construct()
    {
        $this->pagoModel       = new PagosModel();
        $this->contratoModel   = new ContratosModel();
        $this->formasPagoModel = new FormasPagoModel();
    }

    public function listar(?int $idContrato = null): array
    {
        return $this->pagoModel->listarConDetalles($idContrato);
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->pagoModel->obtenerConDetalle($id);
    }

    public function registrar(array $data): array
    {
        $contrato = $this->contratoModel->find((int) $data['id_contrato']);

        if (!$contrato) {
            throw new \RuntimeException('Contrato no encontrado', 404);
        }

        if ($contrato['estado'] !== 'ACTIVO') {
            throw new \RuntimeException('Solo se pueden registrar pagos en contratos ACTIVOS', 409);
        }

        $sumPagos = $this->pagoModel->sumarPorContrato((int) $data['id_contrato']);

        $saldo = (float) $contrato['total'] - (float) $contrato['adelanto'] - $sumPagos;

        if ((float) $data['monto'] > $saldo + 0.001) {
            throw new \RuntimeException(
                json_encode(['message' => 'El monto excede el saldo pendiente', 'saldo' => round($saldo, 2)]),
                409
            );
        }

        $fechaStr  = $data['fecha'] ?? date('Y-m-d');
        $fechaPago = \DateTime::createFromFormat('Y-m-d', $fechaStr);

        if (!$fechaPago) {
            throw new \RuntimeException('Formato de fecha inválido. Use YYYY-MM-DD.', 422);
        }

        $hoy      = new \DateTime('today');
        $minFecha = (clone $hoy)->modify('-3 days');
        $fechaPago->setTime(0, 0, 0);

        if ($fechaPago > $hoy) {
            throw new \RuntimeException('La fecha de pago no puede ser en el futuro.', 422);
        }

        if ($fechaPago < $minFecha) {
            throw new \RuntimeException('La fecha de pago no puede ser anterior a 3 días de hoy.', 422);
        }

        $idPago = $this->pagoModel->insert([
            'id_contrato'  => $data['id_contrato'],
            'id_form_pago' => $data['id_form_pago'],
            'monto'        => $data['monto'],
            'moneda'       => $data['moneda'] ?? 'PEN',
            'voucher'      => $data['voucher'] ?? null,
            'fecha'        => $fechaStr,
        ]);

        if ($idPago === false) {
            throw new \RuntimeException(json_encode($this->pagoModel->errors()), 422);
        }

        $nuevoTotalPagado = (float) $contrato['adelanto'] + $sumPagos + (float) $data['monto'];
        $nuevoSaldo       = (float) $contrato['total'] - $nuevoTotalPagado;

        if ($nuevoSaldo <= 0) {
            $this->contratoModel->update((int) $data['id_contrato'], ['estado' => 'COMPLETADO']);
        }

        return [
            'id_pago'      => $idPago,
            'total_pagado' => round($nuevoTotalPagado, 2),
            'saldo'        => round($nuevoSaldo, 2),
            'completado'   => $nuevoSaldo <= 0,
        ];
    }

    public function anular(int $id): void
    {
        $pago = $this->pagoModel->find($id);

        if (!$pago) {
            throw new \RuntimeException('Pago no encontrado', 404);
        }

        $contrato = $this->contratoModel->find((int) $pago['id_contrato']);

        if ($contrato['estado'] !== 'ACTIVO') {
            throw new \RuntimeException('No se puede anular pagos de contratos no activos', 409);
        }

        $this->pagoModel->delete($id);
    }

    public function formasPago(): array
    {
        return $this->formasPagoModel->listarTodos();
    }
}
