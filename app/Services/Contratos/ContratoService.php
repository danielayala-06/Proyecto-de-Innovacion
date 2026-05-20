<?php

/**
 * @file    ContratoService.php
 * @package App\Services\Contratos
 *
 * Capa de negocio para la gestión de contratos.
 * Aplica todas las reglas que convierten una cotización aprobada
 * en un contrato activo, incluyendo validaciones de antigüedad,
 * estado y control de pagos.
 */

namespace App\Services\Contratos;

use App\Models\ContratosModel;
use App\Models\CotizacionesModel;
use App\Models\CotizacionesDetallesModel;
use App\Models\PagosModel;
use App\Models\ReglasPaquetesModel;

/**
 * Servicio de Contratos.
 *
 * Responsabilidades:
 * - Listar contratos con filtros opcionales.
 * - Crear un contrato validando que la cotización esté APROBADA,
 *   no tenga más de 30 días y no cuente con un contrato activo previo.
 * - Actualizar datos corregibles de un contrato ACTIVO.
 * - Cambiar el estado del contrato (COMPLETADO / CANCELADO).
 * - Retornar el detalle completo de un contrato con historial de pagos
 *   y cálculo de saldo pendiente.
 */
class ContratoService
{
    /** @var ContratosModel Acceso a la tabla `contratos`. */
    protected ContratosModel $contratoModel;

    /** @var CotizacionesModel Acceso a la tabla `cotizaciones`. */
    protected CotizacionesModel $cotizacionModel;

    /** @var PagosModel Acceso a la tabla `pagos`. */
    protected PagosModel $pagoModel;

    /** @var CotizacionesDetallesModel Acceso a los ítems de la cotización. */
    protected CotizacionesDetallesModel $detalleModel;

    /** @var ReglasPaquetesModel Evaluador de reglas de bonificación. */
    protected ReglasPaquetesModel $reglasPaquetesModel;

    public function __construct()
    {
        $this->contratoModel      = new ContratosModel();
        $this->cotizacionModel    = new CotizacionesModel();
        $this->pagoModel          = new PagosModel();
        $this->detalleModel       = new CotizacionesDetallesModel();
        $this->reglasPaquetesModel = new ReglasPaquetesModel();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lista todos los contratos, opcionalmente filtrados.
     *
     * @param  array<string, mixed> $filters Filtros admitidos: estado, id_cliente.
     * @return array<int, array<string, mixed>>
     */
    public function listar(array $filters = []): array
    {
        return $this->contratoModel->listarCompleto($filters);
    }

    /**
     * Retorna un contrato con sus datos de cliente, cotización y pagos,
     * incluyendo el cálculo de total pagado y saldo pendiente.
     *
     * El adelanto inicial se incluye como el primer registro del historial
     * de pagos para mantener una vista unificada.
     *
     * @param  int                       $id ID del contrato.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function buscarPorID(int $id): ?array
    {
        $contrato = $this->contratoModel->obtenerConCliente($id);
        if (!$contrato) {
            return null;
        }

        $pagosAdicionales = $this->pagoModel->historialPorContrato($id);

        $adelanto    = (float) $contrato['adelanto'];
        $total       = (float) $contrato['total'];
        $sumPagos    = array_sum(array_column($pagosAdicionales, 'monto'));
        $totalPagado = $adelanto + $sumPagos;

        // El adelanto se presenta como primer pago del historial
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

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea un contrato a partir de una cotización aprobada.
     *
     * Reglas de negocio aplicadas:
     * 1. La cotización debe existir.
     * 2. No puede estar en estado EXPIRADA.
     * 3. Debe estar en estado APROBADA.
     * 4. No puede tener más de 30 días de antigüedad.
     * 5. No debe existir ya un contrato ACTIVO para esa cotización.
     * 6. El adelanto no puede superar el total de la cotización.
     *
     * @param  array<string, mixed> $data Campos requeridos:
     *                                    id_cotizacion, adelanto, fecha_emision?, observaciones?
     * @return array{id_contrato: int, total: float, saldo: float}
     *
     * @throws \RuntimeException 404 si la cotización no existe.
     * @throws \RuntimeException 409 si alguna regla de negocio impide la creación.
     * @throws \RuntimeException 422 si el adelanto supera el total o falla validación.
     */
    public function crear(array $data): array
    {
        $cotizacion = $this->cotizacionModel->find((int) $data['id_cotizacion']);

        if (!$cotizacion) {
            throw new \RuntimeException('Cotización no encontrada', 404);
        }

        if ($cotizacion['estado'] === 'EXPIRADA') {
            throw new \RuntimeException(
                'La cotización ha expirado (más de 30 días). Ya no puede convertirse en contrato',
                409
            );
        }

        if ($cotizacion['estado'] !== 'APROBADA') {
            throw new \RuntimeException(
                'La cotización debe estar APROBADA para generar un contrato',
                409
            );
        }

        $fechaCot = strtotime($cotizacion['fecha_registro']);
        if ($fechaCot && (time() - $fechaCot) > 30 * 86400) {
            throw new \RuntimeException(
                'La cotización tiene más de 30 días y no puede convertirse en contrato',
                409
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
                'El adelanto no puede superar el total de la cotización',
                422
            );
        }

        $detalles   = $this->detalleModel->where('id_cotizacion', (int) $data['id_cotizacion'])->findAll();
        $evaluacion = $this->reglasPaquetesModel->evaluarDetalles($detalles);

        $db = $this->contratoModel->db;
        $db->transStart();

        $idContrato = $this->contratoModel->insert([
            'id_cotizacion'    => $data['id_cotizacion'],
            'fecha_creacion'   => date('Y-m-d'),
            'fecha_emision'    => $data['fecha_emision'] ?? null,
            'adelanto'         => $adelanto,
            'total'            => $total,
            'observaciones'    => $data['observaciones'] ?? null,
            'estado'           => 'ACTIVO',
            'reglas_aplicadas' => json_encode($evaluacion, JSON_UNESCAPED_UNICODE) ?: null,
        ]);

        if ($idContrato === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->contratoModel->errors()), 422);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            // UNIQUE violation: concurrent request already created a contract
            throw new \RuntimeException('Ya existe un contrato para esta cotización', 409);
        }

        return [
            'id_contrato'      => $idContrato,
            'total'            => $total,
            'saldo'            => $total - $adelanto,
            'reglas_aplicadas' => $evaluacion,
        ];
    }

    /**
     * Actualiza campos corregibles de un contrato ACTIVO (adelanto, fecha, observaciones).
     *
     * Solo modifica los campos presentes en $data.
     * Revalida que el nuevo adelanto no supere el total si se incluye.
     *
     * @param  int                  $id   ID del contrato.
     * @param  array<string, mixed> $data Campos a actualizar (parcial).
     * @return void
     *
     * @throws \RuntimeException 404 si el contrato no existe.
     * @throws \RuntimeException 409 si el contrato no está ACTIVO.
     * @throws \RuntimeException 422 si el adelanto supera el total o falla validación.
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
            if ((float) $data['adelanto'] > (float) $contrato['total']) {
                throw new \RuntimeException(
                    'El adelanto no puede superar el total del contrato',
                    422
                );
            }
            $updateData['adelanto'] = $data['adelanto'];
        }

        if (array_key_exists('fecha_emision', $data)) {
            $updateData['fecha_emision'] = $data['fecha_emision'];
        }
        if (array_key_exists('observaciones', $data)) {
            $updateData['observaciones'] = $data['observaciones'];
        }

        if (!empty($updateData) && $this->contratoModel->update($id, $updateData) === false) {
            throw new \RuntimeException(json_encode($this->contratoModel->errors()), 422);
        }
    }

    /**
     * Cambia el estado de un contrato (COMPLETADO / CANCELADO).
     *
     * Al completar, registra automáticamente la fecha de emisión si aún no existe.
     *
     * @param  int    $id     ID del contrato.
     * @param  string $estado Nuevo estado: 'COMPLETADO' | 'CANCELADO'.
     * @return void
     *
     * @throws \RuntimeException 404 si el contrato no existe.
     * @throws \RuntimeException 422 si falla la validación del modelo.
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

    public function obtenerDataContratoPDF(int $id)
    {
        return $this->contratoModel->obtenerDataPDFContrato($id);
    }
}
