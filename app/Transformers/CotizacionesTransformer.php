<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class CotizacionesTransformer extends BaseTransformer
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
        return [
            'id_cotizacion' => $resource['id_cotizacion'],
            'id_cliente' => $resource['id_cliente'],
            'id_usuario' => $resource['id_usuario'],
            'nombre_cotizacion' => $resource['nombre_cotizacion'],
            'num_dias_vigencia' => $resource['num_dias_vigencia'],
            'fecha_registro' => $resource['fecha_registro'],
            'fecha_hora_inicio' => $resource['fecha_hora_inicio'],
            'fecha_hora_fin' => $resource['fecha_hora_fin'],
            'direccion' => $resource['direccion'],
            'referencia' => $resource['referencia'],
            'latitud' => $resource['latitud'],
            'longitud' => $resource['longitud'],
            'observaciones' => $resource['observaciones'],
            'total_estimado' => $resource['total_estimado'],
            'estado' => $resource['estado'],
        ];
    }
}
