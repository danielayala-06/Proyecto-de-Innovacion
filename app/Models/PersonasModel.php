<?php

/**
 * @file    PersonasModel.php
 * @package App\Models
 *
 * Modelo base para la tabla `personas`.
 * Almacena los datos personales compartidos por clientes, apoderados y usuarios.
 * Las entidades de negocio referencian esta tabla en lugar de duplicar datos.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Personas.
 *
 * Tabla: `personas` (PK: id_persona).
 * Tipos de documento: DNI | CE | PASAPORTE.
 * Teléfono debe tener exactamente 9 dígitos (formato peruano).
 */
class PersonasModel extends Model
{
    protected $table            = 'personas';
    protected $primaryKey       = 'id_persona';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombres',
        'apellidos',
        'telefono',
        'tel_alternativo',
        'correo',
        'tipo_documento',
        'numero_documento',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'nombres'          => 'required|min_length[2]|max_length[100]',
        'apellidos'        => 'permit_empty|max_length[100]',
        'telefono'         => 'required|exact_length[9]',
        'correo'           => 'permit_empty|valid_email|max_length[150]',
        'tipo_documento'   => 'permit_empty|in_list[DNI,CE,PASAPORTE]',
        'numero_documento' => 'permit_empty|max_length[20]',
    ];
    protected $validationMessages = [
        'nombres' => [
            'required'   => 'Los nombres son obligatorios.',
            'min_length' => 'Los nombres deben tener al menos 2 caracteres.',
        ],
        'telefono' => [
            'required'     => 'El teléfono es obligatorio.',
            'exact_length' => 'El teléfono debe tener exactamente 9 dígitos.',
        ],
        'correo' => [
            'valid_email' => 'El correo ingresado no es válido.',
        ],
        'tipo_documento' => [
            'in_list' => 'El tipo de documento no es válido. Use: DNI, CE o PASAPORTE.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
