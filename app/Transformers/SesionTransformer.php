<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class SesionTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        if (!$resource) return [];

        $data = [
            'id_sesion'         => (int)    $resource['id_sesion'],
            'id_promocion'      => (int)    $resource['id_promocion'],
            'nombre_promocion'  =>          $resource['nombre_promocion'] ?? null,
            'grado'             =>          $resource['grado']            ?? null,
            'nombre_colegio'    =>          $resource['nombre_colegio']   ?? null,
            'id_contrato'       => isset($resource['id_contrato']) && $resource['id_contrato']
                                    ? (int) $resource['id_contrato'] : null,
            'fecha_hora_sesion' =>          $resource['fecha_hora_sesion'],
            'tipo'              =>          $resource['tipo'],
            'observaciones'     =>          $resource['observaciones'] ?? null,
            'estado'            =>          $resource['estado'],
        ];

        if (isset($resource['asistencia'])) {
            $data['asistencia'] = array_map(fn($a) => [
                'id_asistencia' => (int) $a['id_asistencia'],
                'id_estudiante' => (int) $a['id_estudiante'],
                'nombres'       => $a['nombres'],
                'apellidos'     => $a['apellidos'],
                'asistio'       => $a['asistio'] === null ? null : (int) $a['asistio'],
            ], $resource['asistencia']);
        }

        return $data;
    }

    protected function getAllowedFields(): ?array
    {
        return null;
    }
}
