<?php

namespace App\Models;

use CodeIgniter\Model;

class Servicio extends Model
{
    protected $table            = 'servicios';
    protected $primaryKey       = 'id_servicio';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_servicio',
        'detalle_servicio',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];


    // Validation
    protected $validationRules      = [
        'nombre_servicio' => 'required|min_length[3]|max_length[150]',
        'detalle_servicio' => 'min_length[3]|max_length[600]',
        'estado' => 'required|in_list[ACTIVO,INACTIVO]',
    ];
    protected $validationMessages   = [
        'nombre_servicio' => [
            'required' => 'El nombre del servicio es requerido',
            'min_length'=>'El nombre de servicio debe tener como minimo 3 caracteres',
            'max_length'=>'El nombre de servicio muy largo',
        ],
        'detalle_servicio' => [
            'min_length'=>'Detalle del servicio debe tener como minimo 3 caracteres',
            'max_length'=>'Detalle del servicio debe tener como maximo 600 caracteres',
        ],
        'estado'=>[
            'required'=>'El estado es requerido',
            'in_list'=>'Debe seleccionar un estado valido: ACTIVO, INACTIVO',
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
