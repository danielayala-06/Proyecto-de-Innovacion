<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Contrato;
use App\Models\Cotizacion;
use App\Transformers\ContratosTransformer;
use CodeIgniter\API\ResponseTrait;

class Contratos extends BaseController
{
    use ResponseTrait;

    public function getIndex(?int $id = null)
    {
        $model       = new Contrato();
        $transformer = new ContratosTransformer();

        if ($id !== null) {
            $contrato = $model->contratoConCliente($id);
            return $contrato
                ? $this->respond($transformer->transform($contrato))
                : $this->failNotFound('Contrato no encontrado');
        }

        return $this->respond(
            $transformer->transformMany($model->contratosConCliente()),
            200,
            'Contratos encontrados'
        );
    }

    public function postIndex()
    {
        $data = json_decode($this->request->getBody(), true);

        if (empty($data['id_cotizacion'])) {
            return $this->failValidationErrors('id_cotizacion es requerido');
        }

        $cotModel = new Cotizacion();
        $cot      = $cotModel->find($data['id_cotizacion']);

        if (!$cot || strtoupper($cot['estado']) !== 'APROBADA') {
            return $this->failValidationErrors('La cotización debe estar en estado APROBADA');
        }

        $model    = new Contrato();
        $existing = $model->where('id_cotizacion', $cot['id_cotizacion'])->first();
        if ($existing) {
            return $this->fail('Ya existe un contrato para esta cotización', 409);
        }

        $nuevo = [
            'id_cotizacion'     => $cot['id_cotizacion'],
            'fecha_contrato'    => $data['fecha_contrato'] ?? date('Y-m-d'),
            'fecha_emision'     => date('Y-m-d'),
            'adelanto'          => (float)($data['adelanto']     ?? 0),
            'observaciones'     => $data['observaciones']        ?? null,
            'fecha_hora_inicio' => $cot['fecha_hora_inicio'],
            'fecha_hora_fin'    => $cot['fecha_hora_fin'],
            'estado'            => 'ACTIVO',
            'total_final'       => $cot['total_estimado'],
        ];

        if (!$model->insert($nuevo)) {
            return $this->fail($model->errors() ?: 'Error al crear el contrato', 500);
        }

        $transformer = new ContratosTransformer();
        return $this->respondCreated(
            $transformer->transform($model->contratoConCliente($model->getInsertID()))
        );
    }

    public function putIndex(int $id)
    {
        $model    = new Contrato();
        $contrato = $model->find($id);

        if (!$contrato) {
            return $this->failNotFound('Contrato no encontrado');
        }

        $data = json_decode($this->request->getBody(), true);
        if (empty($data)) {
            return $this->failValidationErrors('Cuerpo de la petición inválido');
        }

        $permitidos = ['estado', 'observaciones', 'adelanto', 'total_final'];
        $update     = array_intersect_key($data, array_flip($permitidos));

        if (empty($update)) {
            return $this->failValidationErrors('No hay campos válidos para actualizar');
        }

        $model->update($id, $update);

        $transformer = new ContratosTransformer();
        return $this->respond(
            $transformer->transform($model->contratoConCliente($id)),
            200,
            'Contrato actualizado'
        );
    }

    public function deleteIndex(int $id)
    {
        $model    = new Contrato();
        $contrato = $model->find($id);

        if (!$contrato) {
            return $this->failNotFound('Contrato no encontrado');
        }

        $model->update($id, ['estado' => 'CANCELADO']);
        return $this->respondDeleted(['message' => 'Contrato cancelado correctamente']);
    }
}
