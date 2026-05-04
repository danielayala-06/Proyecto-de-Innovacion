<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Producto;
use CodeIgniter\API\ResponseTrait;

class Productos extends BaseController
{
    use ResponseTrait;

    public function getIndex(?int $id = null)
    {
        $model = new Producto();

        if ($id !== null) {
            $item = $model->find($id);
            return $item
                ? $this->respond($item)
                : $this->failNotFound('Producto no encontrado');
        }

        $items = $model->where('estado', 'ACTIVO')->findAll();
        return $this->respond($items, 200, 'Productos encontrados');
    }
}
