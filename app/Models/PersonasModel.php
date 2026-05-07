<?php

namespace App\Models;

use CodeIgniter\Model;

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
        'telefonos',
        'tel_alternativo',
        'correo',
        'tipo_documento',
        'numero_documento'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'nombres' => 'required|min_length[2]|max_length[100]',
        'apellidos' => 'required|min_length[2]|max_length[100]',
        'telefonos' => 'required|min_length[7]|max_length[20]',
        'correo' => 'permit_empty|valid_email|max_length[150]',
        'tipo_documento' => 'required|in_list[DNI,CE,PASAPORTE]',
        'numero_documento' => 'required|min_length[8]|max_length[20]'
    ];
    protected $validationMessages = [
        'nombres' => [
            'required' => 'Los nombres son obligatorios.',
            'min_length' => 'Los nombres deben tener al menos 2 caracteres.'
        ],
        'apellidos' => [
            'required' => 'Los apellidos son obligatorios.'
        ],
        'telefonos' => [
            'required' => 'El teléfono es obligatorio.'
        ],
        'correo' => [
            'valid_email' => 'El correo ingresado no es válido.'
        ],
        'tipo_documento' => [
            'in_list' => 'El tipo de documento no es válido.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
