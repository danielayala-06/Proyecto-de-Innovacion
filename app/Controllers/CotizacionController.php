<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Cliente;
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

    public function searchCliente()
    {
        $data = $this->request->getJSON(true);

        $tipo = $data['tipo'] ?? null;
        $valor = $data['valor'] ?? null;

        // Validación básica
        if (!$tipo || !$valor) {
            return $this->fail('Faltan datos');
        }

        // Validar columnas permitidas (IMPORTANTE)
        $allowed = ['numero_documento', 'telefono', 'nombres'];
        if (!in_array($tipo, $allowed)) {
            return $this->fail('Tipo de búsqueda inválido');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('personas');

        // Aplicar filtro
        if ($tipo === 'dni') {
            $builder->where($tipo, $valor); // exacto
        } else {
            $builder->like($tipo, $valor); // parcial
        }

        $query = $builder->get();
        $clientes = $query->getResult();

        if (empty($clientes)) {
            return $this->failNotFound('No se encontraron resultados');
        }

        return $this->respond($clientes, 200);
    }
    public function fetchCotizaciones()
    {

    }
}
