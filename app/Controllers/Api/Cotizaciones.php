<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Api\ResponseTrait;
use App\Models\Cotizacion;
use CodeIgniter\Model;

class Cotizaciones extends BaseController
{
    use ResponseTrait;

    /**
     * Listar uno o algunas cotizaciones
     * GET /api/cotizaciones
     *    y
     * GET /api/cotizaciones/{id}
     */
    public function getIndex(?int $id = null)
    {
        $cotizacion = new Cotizacion();
        
        //Obtiene todos los registros
        if($id==null){
            $lista_cotizacion = $cotizacion->findAll(30);
            return var_dump($lista_cotizacion);
        }

        //Obtiene un registro por id
        return $cotizacion->find($id);

    }

    /**
     * Actualiza una cotizacion
     *
     * PUT /api/cotizacion/{id}
     */
    public function putIndex(int $id)
    {
    }

    /**
     * Crear una nueva cotizacion
     *
     * POST /api/cotizacion
     */
    public function postIndex()
    {
    }

    /**
     * Eliminar un registro
     *
     * DELETE /api/cotizacion/{id}
     */
    public function deleteIndex(int $id)
    {
    }
}
