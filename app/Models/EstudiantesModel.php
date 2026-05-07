<?php

namespace App\Models;

use CodeIgniter\Model;

class EstudiantesModel extends Model
{
    protected $table            = 'estudiantes';
    protected $primaryKey       = 'id_estudiante';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'color_fav',
        'profesion_futura',
        'id_apoderado',
        'id_promocion'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'nombres' => 'required|max_length[100]',
        'apellidos' => 'required|max_length[100]',
        'fecha_nacimiento' => 'permit_empty|valid_date',
        'id_apoderado' => 'required|is_natural_no_zero',
        'id_promocion' => 'required|is_natural_no_zero'
    ];
    protected $validationMessages = [
        'nombres' => [
            'required' => 'Los nombres del estudiante son obligatorios.'
        ],
        'id_apoderado' => [
            'required' => 'El estudiante debe tener un apoderado.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
