<?php

/**
 * @file    ClienteTransformer.php
 * @package App\Transformers
 *
 * Formateador de respuestas JSON para la entidad Cliente.
 * Normaliza los tipos de datos y aplana los campos de persona y cliente
 * en una estructura plana conveniente para el frontend.
 */

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

/**
 * Transformer de Clientes.
 *
 * Entrada: array plano con JOIN de personas + clientes.
 * Salida: objeto JSON listo para consumir en el frontend.
 */
class ClienteTransformer extends BaseTransformer
{
    /**
     * Convierte un registro de cliente en su representación JSON.
     *
     * @param  mixed                $resource Fila del modelo (array plano con JOIN).
     * @return array<string, mixed>           Datos normalizados.
     */
    public function toArray(mixed $resource): array
    {
        if (!$resource) {
            return [];
        }

        return [
            'id_cliente'          => (int)  $resource['id_cliente'],
            'nombres'             =>        $resource['nombres'],
            'apellidos'           =>        $resource['apellidos']           ?? null,
            'telefono'            =>        $resource['telefono']            ?? null,
            'tel_alternativo'     =>        $resource['tel_alternativo']     ?? null,
            'correo'              =>        $resource['correo']              ?? null,
            'numero_documento'    =>        $resource['numero_documento']   ?? null,
            'tipo_documento'      =>        $resource['tipo_documento']     ?? null,
            'red_social'          =>        $resource['red_social']          ?? null,
            'metodo_comunicacion' =>        $resource['metodo_comunicacion'] ?? null,
            'acepta_promociones'  => (bool) ($resource['acepta_promociones'] ?? false),
            'estado'              =>        $resource['estado'],
        ];
    }

    /**
     * @return string[]|null Campos permitidos en la respuesta.
     */
    protected function getAllowedFields(): ?array
    {
        return [
            'id_cliente', 'nombres', 'apellidos', 'telefono', 'tel_alternativo',
            'correo', 'numero_documento', 'tipo_documento', 'red_social',
            'metodo_comunicacion', 'acepta_promociones', 'estado',
        ];
    }
}
