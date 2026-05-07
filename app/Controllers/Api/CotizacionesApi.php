<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\cotizaciones\CotizacionService;
use App\Transformers\CotizacionTransformer;
use CodeIgniter\HTTP\ResponseInterface;

class CotizacionesApi extends BaseController
{
    protected CotizacionService $service;
    protected CotizacionTransformer $transformer;

    public function __construct()
    {
        $this->service = new CotizacionService();
        $this->transformer = new CotizacionTransformer();
    }

    /**
     * GET /api/cotizaciones
     */
    public function index()
    {
        try {

            $cotizaciones = $this->service->listar();
            

            return $this->response
            ->setStatusCode(
                ResponseInterface::HTTP_OK
                )
                ->setJSON([
                    'status' => 'success',
                    'data' => $this->transformer->transformMany($cotizaciones)
                ]);

        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(
                    ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
                )
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    /**
     * GET /api/cotizaciones/{id}
     */
    public function show($id = null)
    {
        try {
            $cotizacion = $this->service->obtenerPorId((int) $id);

            if (!$cotizacion) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Cotización no encontrada'
                    ]);
            }

            return $this->response
                ->setStatusCode(
                    ResponseInterface::HTTP_OK
                )
                ->setJSON([
                    'status' => 'success',
                    'data' => $this->transformer->transform($cotizacion)
                ]);

        } catch (\Throwable $e) {

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    /**
     * POST /api/cotizaciones
     */
    public function create()
    {
        try {
            $body = $this->request->getJSON(true);

            // Validacion basica nomas
            $rules = [
                'id_cliente' => 'required|integer',
                'id_usuario' => 'required|integer',
                'detalles' => 'required'
            ];

            if (! $this->validateData($body, $rules)) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Errores de validación',
                        'errors' => $this->validator->getErrors()
                    ]);
            }

            if (empty($body['detalles'])) {

                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Debe incluir al menos un detalle'
                    ]);
            }

            // Calculamos el total
            $body['total_estimado'] = array_sum(
                array_map(
                    fn($detalle) =>
                        ($detalle['cantidad'] ?? 1)
                        *
                        ($detalle['precio_unitario'] ?? 0),
                    $body['detalles']
                )
            );

            $cotizacion = $this->service->crear($body);

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_CREATED)
                ->setJSON([
                    'status' => 'success',
                    'message' =>'Cotización creada correctamente',
                    'data' => $this->transformer->transform($cotizacion)
                ]);

        } catch (\Throwable $e) {

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    /**
     * PUT /api/cotizaciones/{id}
     */
    public function update($id = null)
    {
        try {

            $body = $this->request->getJSON(true);
            $cotizacion = $this->service->obtenerPorId((int) $id);

            if (! $cotizacion) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Cotización no encontrada'
                    ]);
            }

            // SOLO PENDIENTE
            if (
                $cotizacion['cotizacion']['estado']
                !== 'PENDIENTE'
            ) {

                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Solo se puede editar una cotización BORRADOR'
                    ]);
            }

            // El total se racalcula automaticamente
            if (! empty($body['detalles'])) {
                $body['total_estimado'] = array_sum(
                    array_map(
                        fn($detalle) =>
                            ($detalle['cantidad'] ?? 1)
                            *
                            ($detalle['precio_unitario'] ?? 0),
                        $body['detalles']
                    )
                );
            }

            $updated = $this->service->actualizar(
                (int) $id,
                $body
            );

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON([
                    'status' => 'success',
                    'message' =>'Cotización actualizada',
                    'data' => $this->transformer->transform($updated)
                ]);

        } catch (\Throwable $e) {

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    /**
     * PATCH /api/cotizaciones/{id}/estado
     */
    public function cambiarEstado($id = null)
    {
        try {
            $body = $this->request->getJSON(true);
            $estado = strtoupper($body['estado'] ?? '');
            $validos = ['BORRADOR','APROBADA','RECHAZADA'];

            if (! in_array($estado, $validos)) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Estado inválido'
                    ]);
            }

            $cotizacion = $this->service->obtenerPorId((int) $id);

            if (!$cotizacion) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Cotización no encontrada'
                    ]);
            }

            // actualizamos el estado
            $this->service->actualizar(
                (int) $id,
                [
                    'estado' => $estado,
                    'observaciones' =>
                        $cotizacion['cotizacion']['observaciones'],
                    'total_estimado' =>
                        $cotizacion['cotizacion']['total'],
                    'detalles' =>
                        $cotizacion['detalles']
                ]
            );

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON([
                    'status' => 'success',
                    'message' =>"Estado cambiado a {$estado}"
                ]);

        } catch (\Throwable $e) {

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    /**
     * DELETE /api/cotizaciones/{id}
     */
    public function delete($id = null)
    {
        try {
            $cotizacion = $this->service->obtenerPorId((int) $id);

            if (! $cotizacion) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Cotización no encontrada'
                    ]);
            }

            if (
                $cotizacion['cotizacion']['estado']
                !== 'PENDIENTE'
            ) {

                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                    ->setJSON([
                        'status' => 'error',
                        'message' =>'Solo se puede rechazar una cotización PENDIENTE'
                    ]);
            }

            // Una cotizaion no se elimina se rechaza!!!!
            $this->service->actualizar(
                (int) $id,
                ['estado'=>'RECHAZADA']
            );

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON([
                    'status' => 'success',
                    'message' =>'Cotización rechazada!'
                ]);

        } catch (\Throwable $e) {

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }
}
