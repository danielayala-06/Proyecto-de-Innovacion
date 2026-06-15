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
use App\Models\PromocionesEscolaresModel;
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

    /** @var PromocionesEscolaresModel Para activar la promoción al confirmar el contrato. */
    protected PromocionesEscolaresModel $promocionModel;

    /** @var ReglasPaquetesModel Evaluador de reglas de bonificación. */
    protected ReglasPaquetesModel $reglasPaquetesModel;

    public function __construct()
    {
        $this->contratoModel       = model(ContratosModel::class);
        $this->cotizacionModel     = model(CotizacionesModel::class);
        $this->pagoModel           = model(PagosModel::class);
        $this->detalleModel        = model(CotizacionesDetallesModel::class);
        $this->promocionModel      = model(PromocionesEscolaresModel::class);
        $this->reglasPaquetesModel = model(ReglasPaquetesModel::class);
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

        $pagos       = $this->pagoModel->historialPorContrato($id);
        $total       = (float) $contrato['total'];
        $totalPagado = array_sum(array_column($pagos, 'monto'));

        $contrato['pagos']        = $pagos;
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
            ->where('estado !=', 'CANCELADO')
            ->first();

        if ($existente) {
            throw new \RuntimeException(
                'Ya existe un contrato vigente para esta cotización. Solo se puede generar uno nuevo si el contrato anterior fue cancelado.',
                409
            );
        }

        $adelanto = round((float) $data['adelanto'], 2);
        $total    = round((float) $cotizacion['total_estimado'], 2);

        if ($adelanto > $total) {
            throw new \RuntimeException(
                'El adelanto no puede superar el total de la cotización',
                422
            );
        }

        $promocion    = $this->promocionModel->porCotizacion((int) $data['id_cotizacion']);
        $numAlumnos   = (int) ($promocion['num_estudiantes'] ?? 0);
        $minEst       = $numAlumnos > 0 ? $numAlumnos * 10 : 0;
        $minPct       = (int) ceil($total * 0.05);
        $adelantoMin  = max(50, $minEst, $minPct);

        if ($adelanto < $adelantoMin) {
            throw new \RuntimeException(
                "El adelanto mínimo es S/ {$adelantoMin} " .
                ($numAlumnos > 0
                    ? "(S/ 10 × {$numAlumnos} alumnos o 5% del total, lo que sea mayor)"
                    : "(5% del total)"),
                422
            );
        }

        $detalles   = $this->detalleModel->where('id_cotizacion', (int) $data['id_cotizacion'])->findAll();
        $evaluacion = $this->reglasPaquetesModel->evaluarDetalles($detalles);

        $db = $this->contratoModel->db;
        $db->transStart();

        $idContrato = $this->contratoModel->insert([
            'id_cotizacion'     => $data['id_cotizacion'],
            'fecha_creacion'    => date('Y-m-d'),
            'fecha_emision'     => $data['fecha_emision']      ?? null,
            'adelanto'          => $adelanto,
            'total'             => $total,
            'contacto2_nombre'  => $data['contacto2_nombre']   ?? null,
            'contacto2_telefono'=> $data['contacto2_telefono'] ?? null,
            'observaciones'     => $data['observaciones']      ?? null,
            'estado'            => 'ACTIVO',
            'reglas_aplicadas'  => json_encode($evaluacion, JSON_UNESCAPED_UNICODE) ?: null,
        ]);

        if ($idContrato === false) {
            $db->transRollback();
            $modelErrors = $this->contratoModel->errors();
            if (!empty($modelErrors)) {
                throw new \RuntimeException(implode(' | ', $modelErrors), 422);
            }
            $dbErr = $this->contratoModel->db->error();
            $msg   = !empty($dbErr['message']) ? $dbErr['message'] : 'Error al registrar el contrato';
            throw new \RuntimeException($msg, 500);
        }

        // Activar la promoción vinculada a esta cotización (se creó en estado inactivo)
        $this->promocionModel
            ->where('id_cotizacion', (int) $data['id_cotizacion'])
            ->set(['is_active' => true])
            ->update();

        // Registrar el adelanto como primer pago
        $formaPago = !empty($data['forma_pago']) ? trim($data['forma_pago']) : 'Efectivo';
        $this->pagoModel->insert([
            'id_contrato' => $idContrato,
            'forma_pago'  => $formaPago,
            'monto'       => $adelanto,
            'moneda'      => 'PEN',
            'fecha'       => date('Y-m-d'),
        ]);

        // Registrar sesiones opcionales vinculadas a la promoción
        $sesionesInput = $data['sesiones'] ?? [];
        if (!empty($sesionesInput) && is_array($sesionesInput) && $promocion !== null) {
            try {
                service('sesionService')->crearDesdeContrato($sesionesInput, (int) $promocion['id_promocion']);
            } catch (\RuntimeException $e) {
                $db->transRollback();
                throw $e;
            }
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

        $updateData    = [];
        $nuevoAdelanto = null;

        if (isset($data['adelanto'])) {
            $nuevoAdelanto = round((float) $data['adelanto'], 2);

            if ($nuevoAdelanto > (float) $contrato['total']) {
                throw new \RuntimeException(
                    'El adelanto no puede superar el total del contrato',
                    422
                );
            }

            $promocion   = $this->promocionModel->porCotizacion((int) $contrato['id_cotizacion']);
            $numAlumnos  = (int) ($promocion['num_estudiantes'] ?? 0);
            $totalContrato = (float) $contrato['total'];
            $minEst      = $numAlumnos > 0 ? $numAlumnos * 10 : 0;
            $minPct      = (int) ceil($totalContrato * 0.05);
            $adelantoMin = max(50, $minEst, $minPct);

            if ($nuevoAdelanto < $adelantoMin) {
                throw new \RuntimeException(
                    "El adelanto mínimo es S/ {$adelantoMin} " .
                    ($numAlumnos > 0
                        ? "(S/ 10 × {$numAlumnos} alumnos o 5% del total, lo que sea mayor)"
                        : "(5% del total)"),
                    422
                );
            }

            $updateData['adelanto'] = $nuevoAdelanto;
        }

        if (array_key_exists('fecha_emision', $data)) {
            $updateData['fecha_emision'] = $data['fecha_emision'];
        }
        if (array_key_exists('observaciones', $data)) {
            $updateData['observaciones'] = $data['observaciones'];
        }
        if (array_key_exists('contacto2_nombre', $data)) {
            $updateData['contacto2_nombre'] = $data['contacto2_nombre'] ?: null;
        }
        if (array_key_exists('contacto2_telefono', $data)) {
            $updateData['contacto2_telefono'] = $data['contacto2_telefono'] ?: null;
        }

        $db = $this->contratoModel->db;
        $db->transStart();

        if (!empty($updateData) && $this->contratoModel->update($id, $updateData) === false) {
            $db->transRollback();
            throw new \RuntimeException(json_encode($this->contratoModel->errors()), 422);
        }

        // Sincronizar el registro de pago de adelanto cuando cambia el monto
        if ($nuevoAdelanto !== null) {
            $adelantoPago = $this->pagoModel->adelantoPorContrato($id);
            if ($adelantoPago !== null) {
                if ($nuevoAdelanto > 0) {
                    $this->pagoModel->update($adelantoPago['id_pago'], ['monto' => $nuevoAdelanto]);
                } else {
                    $this->pagoModel->delete($adelantoPago['id_pago']);
                }
            } elseif ($nuevoAdelanto > 0) {
                $this->pagoModel->skipValidation(true)->insert([
                    'id_contrato' => $id,
                    'forma_pago'  => 'Efectivo',
                    'monto'       => $nuevoAdelanto,
                    'moneda'      => 'PEN',
                    'fecha'       => date('Y-m-d'),
                ]);
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Error al actualizar el contrato', 500);
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
        $contrato = $this->contratoModel->find($id);
        if (!$contrato) {
            throw new \RuntimeException('Contrato no encontrado', 404);
        }

        $estadoActual = $contrato['estado'];
        $transicionesInvalidas = [
            'COMPLETADO' => ['ACTIVO'],
            'CANCELADO'  => ['ACTIVO', 'COMPLETADO'],
        ];
        if (isset($transicionesInvalidas[$estadoActual]) && in_array($estado, $transicionesInvalidas[$estadoActual], true)) {
            throw new \RuntimeException(
                "No se puede cambiar un contrato {$estadoActual} a {$estado}",
                409
            );
        }

        if ($estado === 'COMPLETADO') {
            $sumPagos = $this->pagoModel->sumarPorContrato($id);
            $saldo    = (float) $contrato['total'] - $sumPagos;
            if ($saldo > 0.01) {
                throw new \RuntimeException(
                    sprintf('No se puede completar el contrato con saldo pendiente de S/ %.2f', $saldo),
                    409
                );
            }
        }

        $updateData = ['estado' => $estado];

        if ($estado === 'COMPLETADO') {
            $updateData['fecha_emision'] = date('Y-m-d');
        }

        if ($estado === 'CANCELADO') {
            $updateData['archivado'] = 1;
        }

        if ($this->contratoModel->update($id, $updateData) === false) {
            throw new \RuntimeException(json_encode($this->contratoModel->errors()), 422);
        }
    }

    /**
     * Alterna el flag `archivado` de un contrato.
     *
     * @param  int  $id ID del contrato.
     * @return bool     Nuevo valor de archivado (true = archivado).
     *
     * @throws \RuntimeException 404 si no existe.
     * @throws \RuntimeException 409 si el estado es ACTIVO.
     */
    public function archivar(int $id): bool
    {
        $row = $this->contratoModel->find($id);
        if (!$row) {
            throw new \RuntimeException('Contrato no encontrado', 404);
        }
        if ($row['estado'] === 'ACTIVO') {
            throw new \RuntimeException('No se puede archivar un contrato vigente.', 409);
        }
        $nuevo = $row['archivado'] ? 0 : 1;
        $this->contratoModel->update($id, ['archivado' => $nuevo]);
        return (bool) $nuevo;
    }

    public function obtenerDataContratoPDF(int $id)
    {
        return $this->contratoModel->obtenerDataPDFContrato($id);
    }
}
