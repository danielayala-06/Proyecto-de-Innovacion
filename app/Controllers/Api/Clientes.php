<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Transformers\ClientesTransformer;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use function PHPUnit\Framework\isEmpty;

class Clientes extends BaseController
{
    use ResponseTrait;

    public function getIndex(?int $id = null)
    {
        $model = new Cliente();
        $transformer = new ClientesTransformer();

        try {
            // Si no se introdujo el ID
            if($id===null){
                $clientes = $model->findAll(30);

                return ($clientes)? $this->respond($clientes):  $this->failNotFound('No hay clientes registrados');
            }

            // Buscamos por el ID
            $cliente = $model->find($id);

            // Devolvemos los datos en JSON
            return ($cliente) ? $this->respond(($transformer->transform($cliente))):
                $this->failNotFound('Cliente no encontrado');


        }catch (\Exception $e){
            throw $e;
        }
    }
}
