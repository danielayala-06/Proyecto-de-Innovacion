<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class ContratoTransformer extends BaseTransformer
{
    /**
     * Transform the resource into an array.
     *
     * @param mixed $resource
     *
     * @return array<string, mixed>
     */
     public function toArray(mixed $resource): array
     {
        if (!$resource) return [];

        return [
            'id' =>(int)$resource['id_contrato'],
            'fecha_creacion' =>$resource['fecha_creacion'],
            'fecha_emision' => $resource['fecha_emision'],
            'adelanto' =>(float)$resource['adelanto'],
            'total' =>(float)$resource['total'],
            'estado' =>$resource['estado'],
            'cliente' => [
                'nombre' =>$resource['cliente'],
                'telefono' =>$resource['telefono']
            ],
            'cotizacion' => [
                'total_estimado' =>(float)$resource['total_estimado'],
            ]
        ];
    }

    /**
     * FIELDS PERMITIDOS
     */
    protected function getAllowedFields(): ?array
    {
        return [
            'id',
            'fecha_creacion',
            'fecha_emision',
            'adelanto',
            'total',
            'estado',
            'cliente',
            'cotizacion'
        ];

    }
}