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
    public function toArray(mixed $resource, bool $withPersonas = false): array
    {
        $data = [
            'id_cliente' => $resource['id_cliente'],
            'red_social' => $resource['red_social'],
            'id_persona' => $resource['id_persona'],
            'id_empresa' => $resource['id_empresa'],
            'metodo_comunicacion' => $resource['metodo_comunicacion'],
            'estado' => $resource['estado'],
        ];

        $data['persona'] = [
            'nombres' => $resource['nombres']?? null,
            'apellidos' => $resource['apellidos'] ?? null,
            'telefono' => $resource['telefono'] ?? null,
            'correo' => $resource['persona_correo'] ?? null,
        ];

        return $data;
    }
}
