<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Cotizacion;
use App\Models\Views\CotizacionesFull; //Modelo de la vista Cotizaciones full
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Api\ResponseTrait;
use App\Transformers\CotizacionesTransformer;

class Cotizaciones extends BaseController
{
    use ResponseTrait;

    /**
     * Listar uno o algunas cotizaciones
     * GET /api/cotizaciones
     *    y
     * GET /api/cotizaciones/{id}
     */
    public function getIndex(?int $id = null)
    {
        // Modelo de la cotizacion (Acceso a la BD)
        $model = new Cotizacion();

        // Transformador de la cotizacion
        $transformer = new CotizacionesTransformer();
        
        //Obtiene todos los registros
        if ($id === null) {
            $cotizaciones = $model->findAll(30);

            return  $this->respond($transformer->transformMany($cotizaciones));
        }

        $cotizacion = $model->find($id);

        if (!$cotizacion) {
            return $this->failNotFound('Cotizacion no encontrada');

        }

        return $this->respond($transformer->transform($cotizacion));
    }

    /**
     * Actualiza una cotizacion
     *
     * PUT /api/cotizacion/{id}
     */
    public function putIndex(int $id)
    {
    }

    /**
     * Crear una nueva cotizacion
     *
     * POST /api/cotizacion
     */
    public function postIndex()
    {
    }

    /**
     * Eliminar un registro
     *
     * DELETE /api/cotizacion/{id}
     */
    public function deleteIndex(int $id)
    {
    }
}
