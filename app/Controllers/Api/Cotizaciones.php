<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Views\CotizacionesFull; //Modelo de la vista Cotizaciones
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
        $model = new CotizacionesFull();
        $transformer = new CotizacionesTransformer();
        
        //Obtiene todos los registros
        if ($id === null) {
            return $this->paginate($model, 20, CotizacionesTransformer::class);
        }

        $cotizacion = $model->find($id);

        if (!$cotizacion) {
            return $this->failNotFound('No encontrado');
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
