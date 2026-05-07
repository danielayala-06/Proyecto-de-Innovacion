<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class CotizacionTransformer extends BaseTransformer
{
    /**
     * @return string[]|null
     */
    protected function getAllowedFields(): ?array
    {
        return [
            'id',
            'fecha',
            'estado',
            'total',
            'observaciones',
            'cliente',
            'usuario',
            'detalles'
        ];
    }

    /**
     * Includes permitidos
     */
    protected function getAllowedIncludes(): ?array
    {
        return [
            'cliente',
            'usuario',
            'detalles'
        ];
    }

    /**
     * Transform principal
     */
    public function toArray(mixed $resource): array
    {
        if ($resource === null) {
            return [];
        }

        return [
            'id' => (int)$resource['cotizacion']['id'],
            'fecha' =>$resource['cotizacion']['fecha'],
            'estado' =>$resource['cotizacion']['estado'],
            'total' => (float)$resource['cotizacion']['total'],
            'observaciones' =>$resource['cotizacion']['observaciones'],
            
            // Includes directos
            'cliente' => $this->includeCliente(),
            'usuario' => $this->includeUsuario(),
            'items' => $this->includeDetalles()
        ];
    }

    /**
     * INCLUDE CLIENTE
     */
    protected function includeCliente(): array
    {
        return [

            'id' =>
                (int)$this->resource['cliente']['id'],

            'nombre_completo' =>
                $this->resource['cliente']['nombre_completo']
        ];
    }

    /**
     * INCLUDE USUARIO
     */
    protected function includeUsuario(): array
    {
        return [
            'username' => $this->resource['usuario']['username']
        ];
    }

    /**
     * INCLUDE DETALLES
     */
    protected function includeDetalles(): array
    {
        return array_map(
            fn($detalle) => [
                'id' =>(int)$detalle['id'],
                'tipo_item' =>$detalle['tipo_item'],
                'descripcion' =>$detalle['descripcion'],
                'referencia_nombre' =>$detalle['referencia_nombre'],
                'cantidad' =>(int)$detalle['cantidad'],
                'precio_unitario' =>(float)$detalle['precio_unitario'],
            ],

            $this->resource['detalles'] ?? []
        );
    }

}