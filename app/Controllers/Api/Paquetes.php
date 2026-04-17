<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Api\ResponseTrait;


class Paquetes extends BaseController
{
    use ResponseTrait;

    /**
     * Listar uno o mas paquetes
     * GET /api/paquetes
     *    y
     * GET /api/paquetes/{id}
     */
    public function getIndex(?int $id = null)
    {
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
