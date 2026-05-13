<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class PaqueteTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        if (!$resource) return [];

        $data = [
            'id_paquete'       => (int) $resource['id_paquete'],
            'nombre_paquete'   => $resource['nombre_paquete'],
            'nivel_disponible' => $resource['nivel_disponible'],
            'descripcion'      => $resource['descripcion'] ?? null,
            'imagen'           => $resource['imagen'] ?? null,
            'precio'           => (float) $resource['precio'],
            'categoria'        => $resource['categoria'] ?? null,
            'estado'           => $resource['estado'],
        ];

        if (isset($resource['num_productos'])) {
            $data['num_productos'] = (int) $resource['num_productos'];
        }

        if (isset($resource['productos'])) {
            $data['productos'] = array_map(fn($p) => [
                'id_paquete_prod' => (int) $p['id_paquete_prod'],
                'id_producto'     => (int) $p['id_producto'],
                'nombre_producto' => $p['nombre_producto'],
                'categoria'       => $p['categoria'] ?? null,
                'tamanio'         => $p['tamanio'] ?? null,
                'estado'          => $p['estado'],
                'cantidad'        => (int) $p['cantidad'],
            ], $resource['productos']);
        }

        if (isset($resource['reglas'])) {
            $data['reglas'] = $resource['reglas'];
        }

        return $data;
    }

    protected function getAllowedFields(): ?array
    {
        return null;
    }
}
