<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class PromocionTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        if (!$resource) return [];

        $data = [
            'id_promocion'      => (int) $resource['id_promocion'],
            'id_colegio'        => (int) $resource['id_colegio'],
            'id_cotizacion'     => (int) $resource['id_cotizacion'],
            'nombre'            => $resource['nombre'],
            'grado'             => $resource['grado'],
            'seccion'           => $resource['seccion'] ?? null,
            'num_estudiantes'     => (int) $resource['num_estudiantes'],
            'anio'              => (int) $resource['anio'],
            'is_active'         => (bool) $resource['is_active'],
            'nombre_colegio'    => $resource['nombre_colegio'] ?? null,
            'distrito'          => $resource['distrito'] ?? null,
            'total_estimado'    => isset($resource['total_estimado']) ? (float) $resource['total_estimado'] : null,
            'estado_cotizacion' => $resource['estado_cotizacion'] ?? null,
        ];

        if (isset($resource['provincia']))            $data['provincia']            = $resource['provincia'];
        if (isset($resource['cliente']))              $data['cliente']              = $resource['cliente'];
        if (isset($resource['telefono']))             $data['telefono']             = $resource['telefono'];
        if (isset($resource['estudiantes']))          $data['estudiantes']          = $resource['estudiantes'];
        if (isset($resource['sesiones_fotograficas'])) $data['sesiones_fotograficas'] = $resource['sesiones_fotograficas'];

        return $data;
    }

    protected function getAllowedFields(): ?array
    {
        return [
            'id_promocion', 'id_colegio', 'id_cotizacion', 'nombre', 'grado',
            'seccion', 'num_estudiantes', 'anio', 'is_active', 'nombre_colegio',
            'distrito', 'total_estimado', 'estado_cotizacion', 'provincia',
            'cliente', 'telefono', 'estudiantes', 'sesiones_fotograficas',
        ];
    }
}
