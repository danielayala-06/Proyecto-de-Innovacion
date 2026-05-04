<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Servicio;
use CodeIgniter\API\ResponseTrait;

class Servicios extends BaseController
{
    use ResponseTrait;

    public function getIndex(?int $id = null)
    {
        $model = new Servicio();

        if ($id !== null) {
            $item = $model->find($id);
            return $item
                ? $this->respond($item)
                : $this->failNotFound('Servicio no encontrado');
        }

        $items = $model->where('estado', 'ACTIVO')->findAll();
        return $this->respond($items, 200, 'Servicios encontrados');
    }
}
