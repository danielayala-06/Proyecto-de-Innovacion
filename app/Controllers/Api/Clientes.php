<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Cliente;
use App\Transformers\ClientesTransformer;
use CodeIgniter\API\ResponseTrait;

class Clientes extends BaseController
{
    use ResponseTrait;

    public function buscarPorDni()
    {
        $dni = $this->request->getGet('dni') ?? $this->request->getPost('dni');

        // Validaciones
        if (empty($dni)) {
            return $this->failValidationErrors('El DNI es requerido');
        }

        if (!ctype_digit($dni)) {
            return $this->failValidationErrors('El DNI solo debe contener números');
        }

        if (strlen($dni) !== 8) {
            return $this->failValidationErrors('El DNI debe tener exactamente 8 dígitos');
        }

        $token = env('DECOLECTA.KEY');

        if (empty($token)) {
            return $this->failServerError('Token de DECOLECTA no configurado');
        }

        try {
            $client = \Config\Services::curlrequest();

            $response = $client->get(
                'https://api.decolecta.com/v1/reniec/dni?numero=' . $dni,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept'        => 'application/json',
                    ],
                    'timeout' => 10,
                ]
            );

            $data = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200 || empty($data)) {
                return $this->failNotFound('No se encontró información para el DNI ' . $dni);
            }

            return $this->respond($data, 200, 'Consulta DNI exitosa');

        } catch (\Exception $e) {
            return $this->failServerError('Error al consultar DECOLECTA: ' . $e->getMessage());
        }
    }

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
