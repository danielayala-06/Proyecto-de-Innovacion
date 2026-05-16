<?php

/**
 * @file    PromocionTransformer.php
 * @package App\Transformers
 *
 * Formateador de respuestas JSON para la entidad Promoción Escolar.
 * Los campos opcionales (provincia, cliente, estudiantes, sesiones) solo se
 * incluyen cuando el detalle completo es solicitado (GET /api/promociones/{id}).
 */

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

/**
 * Transformer de Promociones Escolares.
 *
 * Entrada: fila del modelo con JOIN de colegios y cotizaciones.
 * Puede incluir opcionalmente: provincia, cliente, telefono,
 * estudiantes[] y sesiones_fotograficas[].
 */
class PromocionTransformer extends BaseTransformer
{
    /**
     * Convierte un registro de promoción en su representación JSON.
     *
     * @param  mixed                $resource Datos de la promoción (con o sin relaciones).
     * @return array<string, mixed>           Datos normalizados.
     */
    public function toArray(mixed $resource): array
    {
        if (!$resource) {
            return [];
        }

        $data = [
            'id_promocion'      => (int)  $resource['id_promocion'],
            'id_colegio'        => (int)  $resource['id_colegio'],
            'id_cotizacion'     => (int)  $resource['id_cotizacion'],
            'nombre'            =>        $resource['nombre'],
            'grado'             =>        $resource['grado'],
            'seccion'           =>        $resource['seccion']          ?? null,
            'num_estudiantes'   => (int)  $resource['num_estudiantes'],
            'anio'              => (int)  $resource['anio'],
            'is_active'         => (bool) $resource['is_active'],
            'nombre_colegio'    =>        $resource['nombre_colegio']   ?? null,
            'distrito'          =>        $resource['distrito']         ?? null,
            'total_estimado'    => isset($resource['total_estimado']) ? (float) $resource['total_estimado'] : null,
            'estado_cotizacion' =>        $resource['estado_cotizacion'] ?? null,
        ];

        if (isset($resource['provincia']))              $data['provincia']              = $resource['provincia'];
        if (isset($resource['cliente']))                $data['cliente']                = $resource['cliente'];
        if (isset($resource['telefono']))               $data['telefono']               = $resource['telefono'];
        if (isset($resource['estudiantes']))            $data['estudiantes']            = $resource['estudiantes'];
        if (isset($resource['sesiones_fotograficas']))  $data['sesiones_fotograficas']  = $resource['sesiones_fotograficas'];

        return $data;
    }

    /**
     * @return string[]|null Campos permitidos en la respuesta.
     */
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
