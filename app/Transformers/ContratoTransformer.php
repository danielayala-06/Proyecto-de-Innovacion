<?php

/**
 * @file    ContratoTransformer.php
 * @package App\Transformers
 *
 * Formateador de respuestas JSON para la entidad Contrato.
 * Los campos opcionales (pagos, total_pagado, saldo) solo se incluyen
 * cuando el detalle completo es solicitado (GET /api/contratos/{id}).
 */

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

/**
 * Transformer de Contratos.
 *
 * Entrada: array del modelo con JOIN de cotización y persona del cliente.
 * Puede incluir opcionalmente: pagos[], total_pagado, saldo.
 */
class ContratoTransformer extends BaseTransformer
{
    /**
     * Convierte un registro de contrato en su representación JSON.
     *
     * @param  mixed                $resource Datos del contrato (con o sin historial de pagos).
     * @return array<string, mixed>           Datos normalizados.
     */
    public function toArray(mixed $resource): array
    {
        if (!$resource) {
            return [];
        }

        $result = [
            'id'             => (int)   $resource['id_contrato'],
            'id_cotizacion'  => (int)   $resource['id_cotizacion'],
            'fecha_creacion' =>         $resource['fecha_creacion'],
            'fecha_emision'  =>         $resource['fecha_emision'],
            'adelanto'       => (float) $resource['adelanto'],
            'total'          => (float) $resource['total'],
            'estado'         =>         $resource['estado'],
            'archivado'      => (bool)  ($resource['archivado'] ?? false),
            'observaciones'  =>         $resource['observaciones'] ?? null,
            'cliente'        => [
                'nombre'   => $resource['cliente'],
                'telefono' => $resource['telefono'],
            ],
            'contacto2'      => ($resource['contacto2_nombre'] ?? null) ? [
                'nombre'   => $resource['contacto2_nombre'],
                'telefono' => $resource['contacto2_telefono'] ?? null,
            ] : null,
            'cotizacion'     => [
                'total_estimado' => (float) $resource['total_estimado'],
            ],
            'promocion'      => !empty($resource['colegio']) ? [
                'colegio'        => $resource['colegio'],
                'grado'          => $resource['grado']          ?? null,
                'seccion'        => $resource['seccion']        ?? null,
                'num_estudiantes'=> isset($resource['num_estudiantes']) ? (int) $resource['num_estudiantes'] : null,
            ] : null,
            'primera_sesion' => $resource['primera_sesion'] ?? null,
        ];

        if (isset($resource['pagos']))             $result['pagos']             = $resource['pagos'];
        if (isset($resource['total_pagado']))      $result['total_pagado']      = (float) $resource['total_pagado'];
        if (isset($resource['saldo']))             $result['saldo']             = (float) $resource['saldo'];
        if (isset($resource['reglas_aplicadas']))  $result['reglas_aplicadas']  = $resource['reglas_aplicadas'];

        return $result;
    }

    /**
     * @return string[]|null Campos permitidos en la respuesta.
     */
    protected function getAllowedFields(): ?array
    {
        return [
            'id', 'id_cotizacion', 'fecha_creacion', 'fecha_emision',
            'adelanto', 'total', 'estado', 'observaciones',
            'cliente', 'contacto2', 'cotizacion', 'promocion', 'pagos', 'total_pagado', 'saldo', 'reglas_aplicadas',
        ];
    }
}
