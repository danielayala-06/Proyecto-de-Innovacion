<?php

/**
 * @file    EstudianteTransformer.php
 * @package App\Transformers
 *
 * Formateador de respuestas JSON para la entidad Estudiante.
 * Incluye los datos del apoderado aplanados en la misma estructura
 * para simplificar el renderizado en el frontend.
 */

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

/**
 * Transformer de Estudiantes.
 *
 * Entrada: fila del modelo EstudiantesModel::listarConApoderado().
 * Salida: objeto JSON con datos del estudiante y su apoderado aplanados.
 */
class EstudianteTransformer extends BaseTransformer
{
    /**
     * Convierte un registro de estudiante en su representación JSON.
     *
     * @param  mixed                $resource Fila del modelo con datos de apoderado.
     * @return array<string, mixed>           Datos normalizados.
     */
    public function toArray(mixed $resource): array
    {
        if (!$resource) {
            return [];
        }

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

    /**
     * @return null Todos los campos son permitidos.
     */
    protected function getAllowedFields(): ?array
    {
        return null;
    }
}
