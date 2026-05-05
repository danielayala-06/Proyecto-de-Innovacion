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
<<<<<<< HEAD
        return [
            'id_paquete' => $resource['id_paquete'],
            'nombre_paquete' => $resource['nombre_paquete'],
            'precio_base' => $resource['precio_base'],
            'imagen' => $resource['imagen'],
            'descripcion' => $resource['descripcion'],
            'categoria' => $resource['categoria'],
            'estado'    => $resource['estado'],
=======
        $paquete   = $resource['paquete'];
        $productos = $resource['productos'] ?? [];
        $servicios = $resource['servicios'] ?? [];



        $data = [
            'id_paquete'     => $paquete['id_paquete'],
            'nombre_paquete' => $paquete['nombre'],
            'precio_base'    => $paquete['precio_base'],
            'imagen'         => $paquete['imagen'] ?? null,
            'descripcion'    => $paquete['descripcion'] ?? null,
            'estado'         => $paquete['estado'] ?? null,
            'productos'      => [],
            'servicios'      => [],
>>>>>>> 5f497efef0ca26de78ddef366e09dfb8f9206ad7
        ];

        // Servicios
        foreach ($servicios as $servicio) {
            $data['servicios'][] = [
                'id_servicio'     => $servicio['id_servicio'],
                'nombre_servicio' => $servicio['nombre'],
            ];
        }

        // Productos
        foreach ($productos as $producto) {
            $data['productos'][] = [
                'id_producto'     => $producto['id_producto'],
                'nombre_producto' => $producto['nombre'],
                'cantidad'        => $producto['cantidad'],
            ];
        }

        return $data;
    }
}
