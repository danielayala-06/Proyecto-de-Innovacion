<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Api\ResponseTrait;
use App\Transformers\PaquetesTransformer;
use App\Models\Paquete;


class Paquetes extends BaseController
{
    use ResponseTrait;

    /**
     * Listar uno o mas paquetes
     * GET /api/paquetes
     *    y
     * GET /api/paquetes/{id}
     */
    public function getIndex(?int $id = null)
    {
        // conexion con la entidad paquete
        $model = new Paquete();

        // Transformador de los paquetes
        $transformer = new PaquetesTransformer();


        if (!$id) {
            $paquetes = $model->findAll();

            return $this->respond($transformer->transformMany($paquetes), 200, 'Paquetes enviados!');
        }

        $paquete = $model->find($id);

        if(!$paquete){
            return $this->failNotFound('No encontrado');
        }

         return $this->respond($transformer->transform($paquete), 200, 'Paquete encontrado!');
    }

    /**
     * Actualiza una paquetes
     *
     * PUT /api/paquetes/{id}
     */
    public function putIndex(int $id)
    {
    }

    /**
     * Crear una nueva paquetes
     *
     * POST /api/paquetes
     */
    public function postIndex()
    {
    }

    /**
     * Eliminar un registro
     *
     * DELETE /api/paquetes/{id}
     */
    public function deleteIndex(int $id)
    {
    }
}
