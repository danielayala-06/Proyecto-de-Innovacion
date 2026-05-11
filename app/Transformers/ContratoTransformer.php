<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class ContratoTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        if (!$resource) return [];

        $result = [
            'id'             => (int)$resource['id_contrato'],
            'id_cotizacion'  => (int)$resource['id_cotizacion'],
            'fecha_creacion' => $resource['fecha_creacion'],
            'fecha_emision'  => $resource['fecha_emision'],
            'adelanto'       => (float)$resource['adelanto'],
            'total'          => (float)$resource['total'],
            'estado'         => $resource['estado'],
            'observaciones'  => $resource['observaciones'] ?? null,
            'cliente' => [
                'nombre'   => $resource['cliente'],
                'telefono' => $resource['telefono'],
            ],
            'cotizacion' => [
                'total_estimado' => (float)$resource['total_estimado'],
            ],
        ];

        if (isset($resource['pagos']))        $result['pagos']        = $resource['pagos'];
        if (isset($resource['total_pagado'])) $result['total_pagado'] = (float)$resource['total_pagado'];
        if (isset($resource['saldo']))        $result['saldo']        = (float)$resource['saldo'];

        return $result;
    }

    protected function getAllowedFields(): ?array
    {
        return [
            'id',
            'id_cotizacion',
            'fecha_creacion',
            'fecha_emision',
            'adelanto',
            'total',
            'estado',
            'observaciones',
            'cliente',
            'cotizacion',
            'pagos',
            'total_pagado',
            'saldo',
        ];
    }
}
