<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Paquete;
use App\Transformers\PaquetesTransformer;
use CodeIgniter\API\ResponseTrait;

class Paquetes extends BaseController
{
    use ResponseTrait;

    public function getIndex(?int $id = null)
    {
        $model       = new Paquete();
        $transformer = new PaquetesTransformer();

        if ($id !== null) {
            $paquete = $model->paqueteFullById($id);
            return $paquete
                ? $this->respond($transformer->transform($paquete))
                : $this->failNotFound('Paquete no encontrado');
        }

        $paquetes = $model->paquetesFull();

        return $this->respond(
            $transformer->transformMany($paquetes),
            200,
            'Paquetes enviados'
        );
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
