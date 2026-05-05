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

        $db  = \Config\Database::connect();
        $row = $db->table('cotizaciones c')
            ->select('c.*, CONCAT_WS(" ", p.nombres, NULLIF(TRIM(p.apellidos), "")) AS cliente,
                      COALESCE(p.telefono, "") AS telefono_cliente')
            ->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left')
            ->join('personas p',  'p.id_persona  = cl.id_persona',  'left')
            ->where('c.id_cotizacion', $id)
            ->get()->getRowArray();

        if (!$row) {
            return $this->failNotFound('Cotización no encontrada');
        }

        $paquetes = $db->table('cotizaciones_paquetes cp')
            ->select('pk.nombre_paquete AS nombre, cp.cantidad, cp.precio_unitario, cp.subtotal')
            ->join('paquetes pk', 'pk.id_paquete = cp.id_paquete', 'left')
            ->where('cp.id_cotizacion', $id)
            ->get()->getResultArray();

        $servicios = $db->table('cotizaciones_servicios cs')
            ->select('s.nombre_servicio AS nombre, cs.cantidad, cs.precio_unitario, cs.subtotal')
            ->join('servicios s', 's.id_servicio = cs.id_servicio', 'left')
            ->where('cs.id_cotizacion', $id)
            ->get()->getResultArray();

        $productos = $db->table('cotizaciones_productos cp')
            ->select('pr.nombre_producto AS nombre, cp.cantidad, cp.precio_unitario, cp.subtotal')
            ->join('productos pr', 'pr.id_producto = cp.id_producto', 'left')
            ->where('cp.id_cotizacion', $id)
            ->get()->getResultArray();

        $data               = $transformer->transform($row);
        $data['paquetes']   = $paquetes;
        $data['servicios']  = $servicios;
        $data['productos']  = $productos;

        return $this->respond($data);
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

    public function patchEstado(int $id)
    {
        $estados = ['pendiente', 'aprobada', 'rechazada', 'completada'];

        $model = new Cotizacion();
        if (!$model->find($id)) {
            return $this->failNotFound('Cotización no encontrada');
        }

        $body   = $this->request->getJSON(true);
        $estado = strtolower(trim($body['estado'] ?? ''));

        if (!in_array($estado, $estados, true)) {
            return $this->failValidationErrors(
                'Estado inválido. Valores permitidos: ' . implode(', ', $estados)
            );
        }

        $model->update($id, ['estado' => $estado]);

        return $this->respond(['id_cotizacion' => $id, 'estado' => $estado], 200, 'Estado actualizado');
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
