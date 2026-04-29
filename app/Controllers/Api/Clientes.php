<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Cliente;
use App\Transformers\ClientesTransformer;
use CodeIgniter\API\ResponseTrait;

class Clientes extends BaseController
{
    use ResponseTrait;

    public function getIndex()
    {
        $model = new Cliente();
        $transformer = new ClientesTransformer();

        $with = $this->request->getGet('with');

        // Validación de parámetro
        if ($with !== null && $with !== 'personas') {
            return $this->failValidationErrors('Campo ' . $with . ' no válido');
        }

        // Con personas
        if ($with === 'personas') {
            $clientes = $model->clientesWithPersona();

            return $this->respond(
                $transformer->transformMany($clientes),
                200,
                'Request exitoso! '
            );
        }

        // Sin relaciones
        $clientes = $model->findAll(30);

        if (!$clientes) {
            return $this->failNotFound('No hay clientes registrados');
        }

        return $this->respond(
            $transformer->transformMany($clientes),
            200,
            'Clientes encontrados'
        );
    }
    public function show(int $id = null)
    {
        $model = new Cliente();
        $transformer = new ClientesTransformer();

        // Buscamos por el ID
        $cliente = $model->find($id);

        // Devolvemos los datos en JSON
        return ($cliente) ? $this->respond(($transformer->transform($cliente))):
            $this->failNotFound('Cliente no encontrado');

    }
}
