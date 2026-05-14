<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class EstudianteTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        if (!$resource) return [];

        return [
            'id_estudiante'       => (int) $resource['id_estudiante'],
            'nombres'             =>       $resource['nombres'],
            'apellidos'           =>       $resource['apellidos'],
            'fecha_nacimiento'    =>       $resource['fecha_nacimiento']    ?? null,
            'color_fav'           =>       $resource['color_fav']           ?? null,
            'profesion_futura'    =>       $resource['profesion_futura']    ?? null,
            'id_apoderado'        => (int) $resource['id_apoderado'],
            'apoderado_nombres'   =>       $resource['apoderado_nombres']   ?? null,
            'apoderado_apellidos' =>       $resource['apoderado_apellidos'] ?? null,
            'apoderado_telefono'  =>       $resource['apoderado_telefono']  ?? null,
            'tipo_relacion'       =>       $resource['tipo_relacion']       ?? null,
        ];
    }

    protected function getAllowedFields(): ?array
    {
        return null;
    }
}
