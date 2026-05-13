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
        'tipo_documento'   => 'required|in_list[DNI,CE,PASAPORTE]',
        'numero_documento' => 'required|min_length[6]|max_length[20]',
    ];
    protected $validationMessages = [
        'nombres' => [
            'required'   => 'Los nombres son obligatorios.',
            'min_length' => 'Los nombres deben tener al menos 2 caracteres.',
        ],
        'telefono' => [
            'required'      => 'El teléfono es obligatorio.',
            'exact_length'  => 'El teléfono debe tener exactamente 9 dígitos.',
        ],
        'correo' => [
            'valid_email' => 'El correo ingresado no es válido.',
        ],
        'tipo_documento' => [
            'in_list' => 'El tipo de documento no es válido. Use: DNI, CE o PASAPORTE.',
        ],
        'numero_documento' => [
            'required' => 'El número de documento es obligatorio.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
