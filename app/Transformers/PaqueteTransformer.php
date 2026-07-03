<?php

/**
 * @file    PaqueteTransformer.php
 * @package App\Transformers
 *
 * Formateador de respuestas JSON para la entidad Paquete.
 * Los campos opcionales (productos, reglas, num_productos) solo se incluyen
 * cuando el detalle completo es solicitado (GET /api/paquetes/{id}).
 */

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

/**
 * Transformer de Paquetes.
 *
 * Entrada: fila del modelo paquetes con campos opcionales productos[] y reglas[].
 * El campo num_productos viene del listado; productos[] viene del detalle.
 */
class PaqueteTransformer extends BaseTransformer
{
    /**
     * Convierte un registro de paquete en su representación JSON.
     *
     * @param  mixed                $resource Fila del modelo (con o sin relaciones).
     * @return array<string, mixed>           Datos normalizados.
     */
    public function toArray(mixed $resource): array
    {
        if (!$resource) {
            return [];
        }

        $nombreImagen = $resource['imagen'] ?? null;

        $data = [
            'id_paquete'       => (int)   $resource['id_paquete'],
            'nombre_paquete'   =>         $resource['nombre_paquete'],
            'nivel_disponible' =>         $resource['nivel_disponible'],
            'descripcion'      =>         $resource['descripcion'] ?? null,
            'imagen'           =>         $nombreImagen,
            'imagen_url'       =>         $nombreImagen ? base_url('images/paquetes/' . $nombreImagen) : null,
            'precio'           => (float) $resource['precio'],
            'categoria'        =>         $resource['categoria']   ?? null,
            'estado'           =>         $resource['estado'],
        ];

        if (isset($resource['num_productos'])) {
            $data['num_productos'] = (int) $resource['num_productos'];
        }

        if (isset($resource['productos'])) {
            $data['productos'] = array_map(fn($p) => [
                'id_paquete_prod' => (int) $p['id_paquete_prod'],
                'id_producto'     => (int) $p['id_producto'],
                'nombre_producto' =>       $p['nombre_producto'],
                'categoria'       =>       $p['categoria'] ?? null,
                'tamanio'         =>       $p['tamanio']   ?? null,
                'estado'          =>       $p['estado'],
                'cantidad'        => (int) $p['cantidad'],
            ], $resource['productos']);
        }

        return $data;
    }

    /**
     * @return null Todos los campos son permitidos.
     */
    protected function getAllowedFields(): ?array
    {
        return null;
    }
}
