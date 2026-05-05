<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Paquete;
use App\Transformers\PaquetesTransformer;
use CodeIgniter\API\ResponseTrait;

class Paquetes extends BaseController
{
    use ResponseTrait;

<<<<<<< HEAD
    public function getIndex(?int $id = null)
=======
    /**
     * Listar uno o mas paquetes
     * GET /api/paquetes
     *    y
     * GET /api/paquetes/{id}
     */
    public function getIndex(int $id = null)
>>>>>>> 5f497efef0ca26de78ddef366e09dfb8f9206ad7
    {
        $model       = new Paquete();
        $transformer = new PaquetesTransformer();

<<<<<<< HEAD
        if ($id !== null) {
            $paquete = $model->find($id);
            return $paquete
                ? $this->respond($transformer->transform($paquete))
                : $this->failNotFound('Paquete no encontrado');
        }

        return $this->respond(
            $transformer->transformMany($model->findAll()),
            200,
            'Paquetes enviados'
        );
=======
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
>>>>>>> 5f497efef0ca26de78ddef366e09dfb8f9206ad7
    }

    public function postIndex()
    {
        $model = new Paquete();
        $data  = json_decode($this->request->getBody(), true);

        if (!$model->insert($data)) {
            return $this->fail($model->errors(), 422);
        }

        $transformer = new PaquetesTransformer();
        return $this->respondCreated(
            $transformer->transform($model->find($model->getInsertID()))
        );
    }

    public function putIndex(int $id)
    {
        $model   = new Paquete();
        $paquete = $model->find($id);

        if (!$paquete) {
            return $this->failNotFound('Paquete no encontrado');
        }

        $data = json_decode($this->request->getBody(), true);
        if (empty($data)) {
            return $this->failValidationErrors('Cuerpo de la petición inválido');
        }

        $model->update($id, $data);

        $transformer = new PaquetesTransformer();
        return $this->respond(
            $transformer->transform($model->find($id)),
            200,
            'Paquete actualizado'
        );
    }

    public function deleteIndex(int $id)
    {
        $model   = new Paquete();
        $paquete = $model->find($id);

        if (!$paquete) {
            return $this->failNotFound('Paquete no encontrado');
        }

        $model->update($id, ['estado' => 'INACTIVO']);
        return $this->respondDeleted(['message' => 'Paquete desactivado correctamente']);
    }
}
