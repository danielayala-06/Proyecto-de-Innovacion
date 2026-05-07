<?php

namespace App\Models;

use CodeIgniter\Model;

class PromocionesEscolaresModel extends Model
{
    protected $table            = 'promociones_escolares';
    protected $primaryKey       = 'id_promocion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'grado',
        'seccion',
        'n_estudiantes',
        'anio',
        'is_active',
        'id_colegio',
        'id_cotizacion'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'nombre' => 'required|max_length[150]',
        'grado' => 'required|max_length[20]',
        'seccion' => 'required|max_length[10]',
        'n_estudiantes' => 'required|integer|greater_than[0]',
        'anio' => 'required|integer|exact_length[4]',
        'id_colegio' => 'required|is_natural_no_zero'
    ];
    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la promoción es obligatorio.'
        ],
        'n_estudiantes' => [
            'greater_than' => 'Debe existir al menos un estudiante.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
