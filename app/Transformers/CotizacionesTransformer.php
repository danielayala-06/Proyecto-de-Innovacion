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
            'fecha_registro' => $resource['fecha_registro'],
            'estado' => $resource['estado'],
            'total_estimado' => $resource['total_estimado'],
            'id_cliente' => $resource['id_cliente'],
            'nombres' => $resource['nombres'],
            'apellidos' => $resource['apellidos'],
            'numero_documento' => $resource['numero_documento'],
            'telefono' => $resource['telefono'],
            'cantidad_paquetes' => $resource['cantidad_paquetes'],
            'total_paquetes' => $resource['total_paquetes'],
            'paquetes' => $resource['paquetes'],
        ];
    }
}
