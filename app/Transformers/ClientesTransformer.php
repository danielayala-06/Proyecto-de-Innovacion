<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class ClientesTransformer extends BaseTransformer
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
            'id_cliente' => $resource['id_cliente'],
            'red_social' => $resource['red_social'],
            'id_persona' => $resource['id_persona'],
            'id_empresa' => $resource['id_empresa'],
            'metodo_comunicacion' => $resource['metodo_comunicacion'],
            'tipo_cliente' => $resource['tipo_cliente'],
            'estado' => $resource['estado'],
        ];
    }
}
