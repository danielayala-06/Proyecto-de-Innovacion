<?php

/**
 * @file    PagoTransformer.php
 * @package App\Transformers
 *
 * Formateador de respuestas JSON para la entidad Pago.
 * Los campos opcionales (total del contrato, estado) solo se incluyen
 * cuando el detalle completo es solicitado (GET /api/pagos/{id}).
 */

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

/**
 * Transformer de Pagos.
 *
 * Entrada: fila del modelo con JOIN de formas_pago, contratos y personas.
 * Los campos `total` y `estado_contrato` son opcionales (solo en vista detalle).
 */
class PagoTransformer extends BaseTransformer
{
    /**
     * Convierte un registro de pago en su representación JSON.
     *
     * @param  mixed                $resource Fila del modelo (con o sin detalle de contrato).
     * @return array<string, mixed>           Datos normalizados.
     */
    public function toArray(mixed $resource): array
    {
        if (!$resource) {
            return [];
        }

        $data = [
            'id_pago'           => (int)   $resource['id_pago'],
            'id_contrato'       => (int)   $resource['id_contrato'],
            'fecha'             =>         $resource['fecha'],
            'monto'             => (float) $resource['monto'],
            'moneda'            =>         $resource['moneda'],
            'voucher'    =>  $resource['voucher']    ?? null,
            'forma_pago' =>  $resource['forma_pago'] ?? null,
            'cliente'    =>  $resource['cliente']    ?? null,
        ];

        if (isset($resource['total']))           $data['total']           = (float) $resource['total'];
        if (isset($resource['estado_contrato'])) $data['estado_contrato'] = $resource['estado_contrato'];

        return $data;
    }

    /**
     * @return string[]|null Campos permitidos en la respuesta.
     */
    protected function getAllowedFields(): ?array
    {
        return [
            'id_pago', 'id_contrato', 'fecha', 'monto', 'moneda', 'voucher',
            'forma_pago', 'cliente', 'total', 'estado_contrato',
        ];
    }
}
