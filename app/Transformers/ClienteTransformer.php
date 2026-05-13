<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class ClienteTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        if (!$resource) return [];

        return [
            'id_cliente'          => (int) $resource['id_cliente'],
            'nombres'             => $resource['nombres'],
            'apellidos'           => $resource['apellidos'] ?? null,
            'telefono'            => $resource['telefono'] ?? null,
            'tel_alternativo'     => $resource['tel_alternativo'] ?? null,
            'correo'              => $resource['correo'] ?? null,
            'numero_documento'    => $resource['numero_documento'],
            'tipo_documento'      => $resource['tipo_documento'],
            'red_social'          => $resource['red_social'] ?? null,
            'metodo_comunicacion' => $resource['metodo_comunicacion'] ?? null,
            'acepta_promociones'  => (bool) ($resource['acepta_promociones'] ?? false),
            'estado'              => $resource['estado'],
        ];
    }

    protected function getAllowedFields(): ?array
    {
        return [
            'id_cliente', 'nombres', 'apellidos', 'telefono', 'tel_alternativo',
            'correo', 'numero_documento', 'tipo_documento', 'red_social',
            'metodo_comunicacion', 'acepta_promociones', 'estado',
        ];
    }
}
