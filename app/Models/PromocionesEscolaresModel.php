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
        'num_estudiantes',
        'anio',
        'is_active',
        'id_colegio',
        'id_cotizacion',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'nombre'          => 'required|max_length[100]',
        'grado'           => 'required|max_length[10]',
        'seccion'         => 'permit_empty|max_length[10]',
        'num_estudiantes' => 'required|integer|greater_than[0]',
        'anio'            => 'required|integer|exact_length[4]',
        'id_colegio'      => 'required|is_natural_no_zero',
    ];
    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la promoción es obligatorio.',
        ],
        'num_estudiantes' => [
            'required'     => 'El número de estudiantes es obligatorio.',
            'greater_than' => 'Debe haber al menos un estudiante.',
        ],
        'grado' => [
            'required' => 'El grado es obligatorio.',
        ],
        'anio' => [
            'required'     => 'El año es obligatorio.',
            'exact_length' => 'El año debe tener 4 dígitos.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
