<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class PaquetesTransformer extends BaseTransformer
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
            'id_paquete' => $resource['id_paquete'],
            'nombre_paquete' => $resource['nombre_paquete'],
            'precio_base' => $resource['precio_base'],
            'imagen' => $resource['imagen'],
            'descripcion' => $resource['descripcion'],
            'estado' => $resource['estado'], 
        ];
    }
}
