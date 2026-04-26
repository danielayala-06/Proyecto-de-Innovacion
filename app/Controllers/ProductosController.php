<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Producto;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class ProductosController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        // Creamos un modelo para acceder a la tabla productos
        $model = new Producto();
        $productos = $model->findAll();

        // En caso de que no haya productos
        if(!$productos) return $this->failNotFound('No se econtraron productos!');

        // Devolvemos los productos
        return $this->respond($productos);
    }
}
