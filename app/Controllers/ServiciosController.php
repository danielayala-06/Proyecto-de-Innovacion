<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Servicio;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class ServiciosController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        // Creamos el modelo
        $model = new Servicio();

        // Obtenemos todos los registros
        $servicios = $model->findAll();

        // En caso de qu eno haya servicios
        if(!$servicios)return $this->failNotFound('No hay servicios encontrados');

        // Devolvemos los servicios en json
        return $this->respond($servicios, 200, 'Servicios encontrados');
    }
}
