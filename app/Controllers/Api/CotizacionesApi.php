<?php

/**
 * @file    CotizacionesApi.php
 * @package App\Controllers\Api
 *
 * Controlador REST para la gestión de cotizaciones.
 * Delega la lógica de negocio en CotizacionService y formatea
 * las respuestas con CotizacionTransformer.
 *
 * Endpoints:
 *   GET    /api/cotizaciones                → listar (todas o solo las disponibles para contrato)
 *   GET    /api/cotizaciones/{id}           → detalle con ítems
 *   POST   /api/cotizaciones                → crear
 *   PUT    /api/cotizaciones/{id}           → actualizar (reemplaza ítems)
 *   PATCH  /api/cotizaciones/{id}/estado    → cambiar estado
 *   DELETE /api/cotizaciones/{id}           → rechazar (soft, no elimina físicamente)
 */

namespace App\Controllers\Api;

use App\Controllers\BaseApiController;
use App\Services\Cotizaciones\CotizacionService;
use App\Transformers\CotizacionTransformer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API de Cotizaciones.
 *
 * Todas las respuestas siguen el formato:
 * { status: 'success'|'error', data?: ..., message?: ..., errors?: ... }
 */
class CotizacionesApi extends BaseApiController
{
    /** @var CotizacionService Servicio con la lógica de negocio de cotizaciones. */
    protected CotizacionService $service;

    /** @var CotizacionTransformer Formateador de respuestas JSON. */
    protected CotizacionTransformer $transformer;

    public function __construct()
    {
        $this->service     = new CotizacionService();
        $this->transformer = new CotizacionTransformer();
    }

    /**
     * GET /api/cotizaciones[?sin_contrato=1]
     *
     * Con `sin_contrato=1` devuelve solo las APROBADAS sin contrato activo
     * (para el selector al generar contratos).
     *
     * @return ResponseInterface 200 con la lista de cotizaciones.
     */
    public function index()
    {
        $sinContrato = $this->request->getGet('sin_contrato');
        $datos       = $sinContrato ? $this->service->listarDisponibles() : $this->service->listar();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status' => 'success',
                'data'   => $this->transformer->transformMany($datos),
            ]);
    }

    /**
     * GET /api/cotizaciones/{id}
     *
     * Verifica la expiración de la cotización antes de devolverla.
     *
     * @param  mixed $id ID de la cotización.
     * @return ResponseInterface 200 con detalle | 404 si no existe.
     */
    public function show($id = null)
    {
        $cotizacion = $this->service->obtenerPorId((int) $id);

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'data' => $this->transformer->transform($cotizacion)]);
    }

    /**
     * POST /api/cotizaciones
     *
     * Body: { id_cliente, id_usuario, detalles: [...], observaciones?,
     *         sesion?: {...}, colegio?: {...} }
     *
     * El total se calcula automáticamente a partir de los detalles.
     *
     * @return ResponseInterface 201 con datos de la cotización creada | 422 si faltan campos.
     */
    public function create()
    {
        $body = $this->request->getJSON(true) ?? [];

        $rules = [
            'id_cliente' => 'required|integer',
            'id_usuario' => 'required|integer',
            'detalles'   => 'required',
        ];

        if (!$this->validateData($body, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        if (empty($body['detalles'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Debe incluir al menos un detalle']);
        }

        $numEstudiantes = (int) (($body['sesion'] ?? [])['num_estudiantes'] ?? 0);
        if ($numEstudiantes <= 0) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'El número de estudiantes es obligatorio y debe ser mayor a 0']);
        }
        if ($numEstudiantes > 1000) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'El número de estudiantes no puede superar 1000']);
        }

        $body['total_estimado'] = $this->_calcularTotal($body['detalles']);

        try {
            $cotizacion = $this->service->crear($body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        $reglasActivadas = $cotizacion['reglas_activadas'] ?? [];

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status'           => 'success',
                'message'          => 'Cotización creada correctamente',
                'data'             => $this->transformer->transform($cotizacion),
                'reglas_activadas' => $reglasActivadas,
            ]);
    }

    /**
     * PUT /api/cotizaciones/{id}
     *
     * Body: { detalles: [...], observaciones? }
     *
     * Reemplaza todos los ítems y recalcula el total.
     * Solo permite actualizar cotizaciones PENDIENTES.
     *
     * @param  mixed $id ID de la cotización.
     * @return ResponseInterface 200 con la cotización actualizada | 404 | 409 | 422.
     */
    public function update($id = null)
    {
        $body = $this->request->getJSON(true) ?? [];

        if (!empty($body['detalles'])) {
            $body['total_estimado'] = $this->_calcularTotal($body['detalles']);
        }

        try {
            $updated = $this->service->actualizar((int) $id, $body);
        } catch (\RuntimeException $e) {
            return $this->serviceError($e);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'  => 'success',
                'message' => 'Cotización actualizada',
                'data'    => $this->transformer->transform($updated),
            ]);
    }

    /**
     * PATCH /api/cotizaciones/{id}/estado
     *
     * Body: { estado: "PENDIENTE" | "APROBADA" | "RECHAZADA" }
     *
     * @param  mixed $id ID de la cotización.
     * @return ResponseInterface 200 | 404 | 422.
     */
    public function cambiarEstado($id = null)
    {
        $body    = $this->request->getJSON(true) ?? [];
        $estado  = strtoupper($body['estado'] ?? '');
        $validos = ['PENDIENTE', 'APROBADA', 'RECHAZADA'];

        if (!in_array($estado, $validos)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['status' => 'error', 'message' => 'Estado inválido. Use: ' . implode(', ', $validos)]);
        }

        $cotizacion = $this->service->obtenerPorId((int) $id);

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        $this->service->cambiarEstado((int) $id, $estado);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => "Estado cambiado a {$estado}"]);
    }

    /**
     * DELETE /api/cotizaciones/{id}
     *
     * Cambia el estado a RECHAZADA. Solo funciona en cotizaciones PENDIENTES.
     * No elimina el registro físicamente.
     *
     * @param  mixed $id ID de la cotización.
     * @return ResponseInterface 200 | 404 | 409.
     */
    public function delete($id = null)
    {
        $cotizacion = $this->service->obtenerPorId((int) $id);

        if (!$cotizacion) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['status' => 'error', 'message' => 'Cotización no encontrada']);
        }

        if ($cotizacion['cotizacion']['estado'] !== 'PENDIENTE') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['status' => 'error', 'message' => 'Solo se puede rechazar una cotización PENDIENTE']);
        }

        $this->service->cambiarEstado((int) $id, 'RECHAZADA');

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['status' => 'success', 'message' => 'Cotización rechazada']);
    }

    /**
     * Calcula el total estimado sumando cantidad × precio_unitario de cada ítem.
     *
     * @param  array<int, array<string, mixed>> $detalles Lista de ítems de la cotización.
     * @return float                                      Total calculado.
     */
    private function _calcularTotal(array $detalles): float
    {
        return (float) array_sum(
            array_map(
                fn($d) => ($d['cantidad'] ?? 1) * ($d['precio_unitario'] ?? 0),
                $detalles
            )
        );
    }

}
