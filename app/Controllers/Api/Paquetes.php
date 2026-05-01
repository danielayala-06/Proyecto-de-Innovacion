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
    public function getIndex(int $id = null)
    {
        // conexion con la entidad paquete
        $model = new Paquete();

        // Transformador de los paquetes
        $transformer = new PaquetesTransformer();

        // Recuperamos los id
        $paquetes = $model->paquetesFull($id);


        if(!$paquetes){
            return $this->failNotFound('No encontrado');
        }

        if($id){
            // Recuperamos el id
            $paquete = $model->paqueteFullById($id);

            if(!$paquete)return $this->failNotFound('Paquete no encontrado');

            return $this->respond(
                $paquete,
                200,
                'ok');
        }

        $paquetesTrans = $transformer->transformMany($paquetes);

        return $this->respond(
            $paquetesTrans,
            200,
            'ok ');
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
