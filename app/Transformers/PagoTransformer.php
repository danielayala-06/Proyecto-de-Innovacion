<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class PagoTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        if (!$resource) return [];

        $data = [
            'id_pago'            => (int) $resource['id_pago'],
            'id_contrato'        => (int) $resource['id_contrato'],
            'fecha'              => $resource['fecha'],
            'monto'              => (float) $resource['monto'],
            'moneda'             => $resource['moneda'],
            'voucher'            => $resource['voucher'] ?? null,
            'nombre_forma_pago'  => $resource['nombre_forma_pago'] ?? null,
            'tipo_pago'          => $resource['tipo_pago'] ?? null,
            'cliente'            => $resource['cliente'] ?? null,
        ];

        if (isset($resource['total']))           $data['total']           = (float) $resource['total'];
        if (isset($resource['estado_contrato'])) $data['estado_contrato'] = $resource['estado_contrato'];

        return $data;
    }

    protected function getAllowedFields(): ?array
    {
        return [
            'id_pago', 'id_contrato', 'fecha', 'monto', 'moneda', 'voucher',
            'nombre_forma_pago', 'tipo_pago', 'cliente', 'total', 'estado_contrato',
        ];
    }
}
