<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Cotizacion;
use App\Transformers\CotizacionesTransformer;
use CodeIgniter\API\ResponseTrait;

class Cotizaciones extends BaseController
{
    use ResponseTrait;

    public function getIndex(?int $id = null)
    {
        $model       = new Cotizacion();
        $transformer = new CotizacionesTransformer();

        if ($id === null) {
            return $this->respond($transformer->transformMany($model->findAll(30)));
        }

        $cotizacion = $model->find($id);
        return $cotizacion
            ? $this->respond($transformer->transform($cotizacion))
            : $this->failNotFound('Cotización no encontrada');
    }

    public function disponibles()
    {
        $db    = \Config\Database::connect();
        $usados = array_column(
            $db->table('contratos')->select('id_cotizacion')->get()->getResultArray(),
            'id_cotizacion'
        );

        $builder = $db->table('cotizaciones cot')
            ->select('cot.id_cotizacion, cot.nombre_cotizacion, cot.total_estimado,
                      cot.fecha_hora_inicio, cot.fecha_hora_fin,
                      CONCAT(p.nombres, " ", p.apellidos) AS cliente, p.telefono')
            ->join('clientes cl', 'cl.id_cliente = cot.id_cliente')
            ->join('personas p',  'p.id_persona = cl.id_persona')
            ->where('UPPER(cot.estado)', 'APROBADA')
            ->orderBy('cot.id_cotizacion', 'DESC');

        if (!empty($usados)) {
            $builder->whereNotIn('cot.id_cotizacion', $usados);
        }

        return $this->respond($builder->get()->getResultArray(), 200, 'Cotizaciones disponibles');
    }

    public function putIndex(int $id)
    {
        $model      = new Cotizacion();
        $cotizacion = $model->find($id);

        if (!$cotizacion) {
            return $this->failNotFound('Cotización no encontrada');
        }

        $data = json_decode($this->request->getBody(), true);
        if (empty($data)) {
            return $this->failValidationErrors('Cuerpo de la petición inválido');
        }

        $model->update($id, $data);

        $transformer = new CotizacionesTransformer();
        return $this->respond(
            $transformer->transform($model->find($id)),
            200,
            'Cotización actualizada'
        );
    }

    public function postIndex()
    {
        $data  = json_decode($this->request->getBody(), true);
        $model = new Cotizacion();

        if (!$model->insert($data)) {
            return $this->fail($model->errors(), 422);
        }

        $transformer = new CotizacionesTransformer();
        return $this->respondCreated(
            $transformer->transform($model->find($model->getInsertID()))
        );
    }

    public function deleteIndex(?int $id = null)
    {
        if (!$id) {
            return $this->fail('Id no encontrado', 400);
        }

        $model      = new Cotizacion();
        $cotizacion = $model->find($id);

        if (!$cotizacion) {
            return $this->failNotFound('Cotización no encontrada');
        }

        $model->update($id, ['estado' => 'RECHAZADA']);

        $transformer = new CotizacionesTransformer();
        return $this->respond(
            $transformer->transform($model->find($id)),
            200,
            'Cotización eliminada'
        );
    }
}
