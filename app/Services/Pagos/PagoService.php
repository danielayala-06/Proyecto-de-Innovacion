<?php

/**
 * @file    PagoService.php
 * @package App\Services\Pagos
 *
 * Capa de negocio para el registro y anulación de pagos en contratos.
 * Controla el saldo pendiente, valida fechas y marca automáticamente
 * el contrato como COMPLETADO cuando el saldo llega a cero.
 */

namespace App\Services\Pagos;

use App\Models\PagosModel;
use App\Models\ContratosModel;
use App\Models\FormasPagoModel;

/**
 * Servicio de Pagos.
 *
 * Responsabilidades:
 * - Listar pagos globales o filtrados por contrato.
 * - Registrar un pago validando saldo disponible y rango de fecha permitido.
 * - Marcar automáticamente el contrato como COMPLETADO al saldar la deuda.
 * - Anular pagos (solo en contratos ACTIVOS).
 * - Proveer el catálogo de formas de pago habilitadas.
 */
class PagoService
{
    /** @var PagosModel Acceso a la tabla `pagos`. */
    protected PagosModel $pagoModel;

    /** @var ContratosModel Acceso a la tabla `contratos`. */
    protected ContratosModel $contratoModel;

    /** @var FormasPagoModel Acceso al catálogo de formas de pago. */
    protected FormasPagoModel $formasPagoModel;

    public function __construct()
    {
        $this->pagoModel       = new PagosModel();
        $this->contratoModel   = new ContratosModel();
        $this->formasPagoModel = new FormasPagoModel();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lista pagos, opcionalmente filtrados por contrato.
     *
     * @param  int|null $idContrato Filtrar por contrato (null = todos).
     * @return array<int, array<string, mixed>>
     */
    public function listar(?int $idContrato = null): array
    {
        return $this->pagoModel->listarConDetalles($idContrato);
    }

    /**
     * Retorna el detalle de un pago con datos del contrato y la forma de pago.
     *
     * @param  int                       $id ID del pago.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerPorId(int $id): ?array
    {
        return $this->pagoModel->obtenerConDetalle($id);
    }

    /**
     * Retorna el catálogo de formas de pago ordenadas alfabéticamente.
     *
     * @return array<int, array<string, mixed>>
     */
    public function formasPago(): array
    {
        return $this->formasPagoModel->listarTodos();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Registra un pago sobre un contrato ACTIVO.
     *
     * Reglas de negocio aplicadas:
     * 1. El contrato debe existir y estar en estado ACTIVO.
     * 2. El monto no puede superar el saldo pendiente.
     * 3. La fecha debe estar entre hoy y 3 días atrás (no se admiten fechas futuras).
     * 4. Si el saldo resultante es ≤ 0, el contrato pasa a COMPLETADO automáticamente.
     *
     * @param  array<string, mixed> $data Campos requeridos:
     *                                    id_contrato, id_form_pago, monto, fecha?,
     *                                    moneda?, voucher?
     * @return array{id_pago: int, total_pagado: float, saldo: float, completado: bool}
     *
     * @throws \RuntimeException 404 si el contrato no existe.
     * @throws \RuntimeException 409 si el contrato no está ACTIVO o el monto supera el saldo.
     * @throws \RuntimeException 422 si la fecha es inválida o falla validación del modelo.
     */
    public function registrar(array $data): array
    {
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

        $db = $this->contratoModel->db;
        $db->transStart();

        // Lock the contract row to prevent concurrent payments from racing
        $contrato = $db->query(
            'SELECT * FROM contratos WHERE id_contrato = ? FOR UPDATE',
            [(int) $data['id_contrato']]
        )->getRowArray();

        if (!$contrato) {
            $db->transRollback();
            throw new \RuntimeException('Contrato no encontrado', 404);
        }

        if ($contrato['estado'] !== 'ACTIVO') {
            $db->transRollback();
            throw new \RuntimeException('Solo se pueden registrar pagos en contratos ACTIVOS', 409);
        }

        $sumPagos = $this->pagoModel->sumarPorContrato((int) $data['id_contrato']);
        $saldo    = (float) $contrato['total'] - (float) $contrato['adelanto'] - $sumPagos;

        if ((float) $data['monto'] > $saldo + 0.001) {
            $db->transRollback();
            throw new \RuntimeException(
                json_encode(['message' => 'El monto excede el saldo pendiente', 'saldo' => round($saldo, 2)]),
                409
            );
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
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->pagoModel->errors()), 422);
        }

        $nuevoTotalPagado = (float) $contrato['adelanto'] + $sumPagos + (float) $data['monto'];
        $nuevoSaldo       = (float) $contrato['total'] - $nuevoTotalPagado;

        if ($nuevoSaldo <= 0) {
            $this->contratoModel->update((int) $data['id_contrato'], ['estado' => 'COMPLETADO']);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al registrar el pago', 500);
        }

        return [
            'id_pago'      => $idPago,
            'total_pagado' => round($nuevoTotalPagado, 2),
            'saldo'        => round($nuevoSaldo, 2),
            'completado'   => $nuevoSaldo <= 0,
        ];
    }

    /**
     * Anula (elimina) un pago de un contrato ACTIVO.
     *
     * No se permite anular pagos de contratos COMPLETADOS o CANCELADOS
     * para preservar la integridad del historial financiero.
     *
     * @param  int  $id ID del pago a anular.
     * @return void
     *
     * @throws \RuntimeException 404 si el pago no existe.
     * @throws \RuntimeException 409 si el contrato no está ACTIVO.
     */
    public function anular(int $id): void
    {
        $pago = $this->pagoModel->find($id);

        if (!$pago) {
            throw new \RuntimeException('Pago no encontrado', 404);
        }

        $contrato = $this->contratoModel->find((int) $pago['id_contrato']);

        if (!$contrato) {
            throw new \RuntimeException('Contrato asociado al pago no encontrado', 404);
        }

        if ($contrato['estado'] !== 'ACTIVO') {
            throw new \RuntimeException('No se puede anular pagos de contratos no activos', 409);
        }

        $this->pagoModel->delete($id);
    }
}
