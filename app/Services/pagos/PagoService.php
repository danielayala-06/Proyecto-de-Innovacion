<?php

namespace App\Services\Pagos;

use App\Models\PagosModel;
use App\Models\ContratosModel;

class PagoService
{
    protected PagosModel     $pagoModel;
    protected ContratosModel $contratoModel;

    public function __construct()
    {
        $this->pagoModel     = new PagosModel();
        $this->contratoModel = new ContratosModel();
    }

    public function listar(?int $idContrato = null): array
    {
        $q = $this->pagoModel
            ->select('pagos.id_pago, pagos.fecha, pagos.monto, pagos.moneda,
                      pagos.voucher, pagos.id_contrato,
                      formas_pago.nombre_forma_pago, formas_pago.tipo_pago,
                      CONCAT(personas.nombres, " ", COALESCE(personas.apellidos,"")) AS cliente')
            ->join('formas_pago', 'formas_pago.id_form_pago = pagos.id_form_pago')
            ->join('contratos',   'contratos.id_contrato = pagos.id_contrato')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',    'personas.id_persona = clientes.id_persona')
            ->orderBy('pagos.fecha', 'DESC');

        if ($idContrato !== null) {
            $q->where('pagos.id_contrato', $idContrato);
        }

        return $q->findAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->pagoModel
            ->select('pagos.*, formas_pago.nombre_forma_pago, formas_pago.tipo_pago,
                      contratos.total, contratos.estado AS estado_contrato,
                      CONCAT(personas.nombres, " ", COALESCE(personas.apellidos,"")) AS cliente')
            ->join('formas_pago', 'formas_pago.id_form_pago = pagos.id_form_pago')
            ->join('contratos',   'contratos.id_contrato = pagos.id_contrato')
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',    'personas.id_persona = clientes.id_persona')
            ->find($id) ?: null;
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

        $sumResult = $this->pagoModel
            ->selectSum('monto', 'total_pagado')
            ->where('id_contrato', $data['id_contrato'])
            ->first();
        $sumPagos = (float) ($sumResult['total_pagado'] ?? 0);

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
        return $this->pagoModel->db
            ->table('formas_pago')
            ->select('id_form_pago, nombre_forma_pago, tipo_pago')
            ->orderBy('nombre_forma_pago', 'ASC')
            ->get()->getResultArray();
    }
}
