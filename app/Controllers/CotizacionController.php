<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Cotizacion;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class CotizacionController extends BaseController
{
    use ResponseTrait;
    public function index(int $page = 1)
    {
        $modelCotizacion = new Cotizacion();// Accedemos al model
        $cotizaciones = $modelCotizacion->getCotizacionesResumen(10, $page);
        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
            'cotizaciones' => $cotizaciones,
        ];

        return view("cotizaciones/index", $data);
    }
    public function create()
    {

        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
        ];

        return view("cotizaciones/crear", $data);
    }
    public function createCotizacion()
    {
        $data   = $this->request->getJSON(true);;

        // Validaciones


        $model = new Cotizacion();
       // $rules = $model->getValidationRules();

        // Insertamos la cotizacion
        $model->insert($data);

        // Obtenemos la nueva cotizacion:
        $newCotizacion = $model->find($model->getInsertID());

        // Enviamos los datos de la nueva cotizacion al endpoint
        return $this->respondCreated($newCotizacion);
    }

    public function fetchCotizaciones()
    {

    }
}
