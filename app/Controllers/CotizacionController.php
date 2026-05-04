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
        $resumenes = $modelCotizacion->getResumenGeneralCoti();

        $cotizaciones = $modelCotizacion->getCotizacionesResumen(10, $page);

        $data = [
            'header' => view("layouts/header"),
            'footer' => view("layouts/footer"),
            'cotizaciones' => $cotizaciones,
            'resumenes' => $resumenes,
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
        $data = $this->request->getJSON(true);

        if (empty($data['cliente'])) {
            return $this->fail('El cliente es requerido', 422);
        }

        if (empty($data['cotizacion'])) {
            return $this->fail('Los datos de la cotización son requeridos', 422);
        }

        $model = new Cotizacion();

        if (!$model->insert($data)) {
            return $this->fail($model->errors() ?: 'Error al crear la cotización', 422);
        }

        return $this->respondCreated($model->find($model->getInsertID()));
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
}
