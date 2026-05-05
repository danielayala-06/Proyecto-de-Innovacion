<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class ContratosTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        $id    = (int)($resource['id_contrato']   ?? 0);
        $idCot = (int)($resource['id_cotizacion'] ?? 0);

        return [
            'id_contrato'       => $id,
            'codigo'            => 'CON-' . str_pad($id,    3, '0', STR_PAD_LEFT),
            'cotizacion_cod'    => 'COT-' . str_pad($idCot, 3, '0', STR_PAD_LEFT),
            'id_cotizacion'     => $idCot,
            'nombre_cotizacion' => $resource['nombre_cotizacion'] ?? null,
            'cliente'           => trim(($resource['nombres'] ?? '') . ' ' . ($resource['apellidos'] ?? '')),
            'telefono'          => $resource['telefono']    ?? null,
            'fecha_contrato'    => $resource['fecha_contrato']    ?? null,
            'fecha_emision'     => $resource['fecha_emision']     ?? null,
            'fecha_inicio'      => $resource['fecha_hora_inicio'] ?? null,
            'fecha_fin'         => $resource['fecha_hora_fin']    ?? null,
            'adelanto'          => (float)($resource['adelanto']   ?? 0),
            'total_final'       => (float)($resource['total_final'] ?? 0),
            'estado'            => strtolower($resource['estado'] ?? 'activo'),
            'observaciones'     => $resource['observaciones'] ?? null,
        ];
    }
}
