<?php

/**
 * @file    CotizacionTransformer.php
 * @package App\Transformers
 *
 * Formateador de respuestas JSON para la entidad Cotización.
 * Recibe la estructura anidada que devuelve CotizacionService (cotizacion, cliente, usuario, detalles)
 * y la aplana en el formato esperado por el frontend.
 */

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

/**
 * Transformer de Cotizaciones.
 *
 * Entrada: array con sub-arrays { cotizacion, cliente, usuario, detalles }.
 * Salida: objeto plano JSON con los includes de cliente, usuario e ítems.
 */
class CotizacionTransformer extends BaseTransformer
{
    /**
     * Convierte la estructura de una cotización en su representación JSON.
     *
     * @param  mixed                $resource Estructura de salida del CotizacionService.
     * @return array<string, mixed>           Datos normalizados.
     */
    public function toArray(mixed $resource): array
    {
        if ($resource === null) {
            return [];
        }

        return [
            'id'             => (int)   $resource['cotizacion']['id'],
            'fecha'          =>         $resource['cotizacion']['fecha'],
            'estado'         =>         $resource['cotizacion']['estado'],
            'total'          => (float) $resource['cotizacion']['total'],
            'observaciones'  =>         $resource['cotizacion']['observaciones'],
            'tiene_contrato' => (bool)  ($resource['cotizacion']['tiene_contrato'] ?? false),
            'cliente'        => $this->includeCliente(),
            'usuario'        => $this->includeUsuario(),
            'items'          => $this->includeDetalles(),
        ];
    }

    /**
     * @return string[]|null Campos permitidos en la respuesta.
     */
    protected function getAllowedFields(): ?array
    {
        return [
            'id', 'fecha', 'estado', 'total', 'observaciones',
            'tiene_contrato', 'cliente', 'usuario', 'items',
        ];
    }

    /**
     * @return string[]|null Includes de relaciones disponibles.
     */
    protected function getAllowedIncludes(): ?array
    {
        return ['cliente', 'usuario', 'detalles'];
    }

    /**
     * Formatea los datos del cliente de la cotización.
     *
     * @return array<string, mixed>
     */
    protected function includeCliente(): array
    {
        return [
            'id'              => (int) $this->resource['cliente']['id'],
            'nombre_completo' =>       $this->resource['cliente']['nombre_completo'],
        ];
    }

    /**
     * Formatea el usuario que registró la cotización.
     *
     * @return array<string, mixed>
     */
    protected function includeUsuario(): array
    {
        return [
            'username' => $this->resource['usuario']['username'],
        ];
    }

    /**
     * Formatea los ítems (paquetes, productos, personalizados) de la cotización.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function includeDetalles(): array
    {
        return array_map(fn($detalle) => [
            'id'                => (int)   $detalle['id'],
            'tipo_item'         =>         $detalle['tipo_item'],
            'id_referencia'     =>         $detalle['id_referencia'] ?? null,
            'descripcion'       =>         $detalle['descripcion'],
            'referencia_nombre' =>         $detalle['referencia_nombre'],
            'cantidad'          => (int)   $detalle['cantidad'],
            'precio_unitario'   => (float) $detalle['precio_unitario'],
        ], $this->resource['detalles'] ?? []);
    }
}
