<?php

namespace App\Models;

use CodeIgniter\Model;

class SesionesFotograficasModel extends Model
{
    protected $table            = 'sesionesfotograficas';
    protected $primaryKey       = 'id_sesion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'fecha_hora_sesion',
        'tipo',
        'observaciones',
        'estado',
        'id_promocion'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'fecha_hora_sesion' => 'required|valid_date',
        'tipo' => 'required|in_list[INDIVIDUAL,GRUPAL,PROMOCIONAL]',
        'estado' => 'required|in_list[PENDIENTE,PROGRAMADA,REALIZADA,EDITANDO,ENTREGADA]',
        'id_promocion' => 'required|is_natural_no_zero'
    ];
    protected $validationMessages = [
        'fecha_hora_sesion' => [
            'required' => 'La fecha de sesión es obligatoria.'
        ],
        'tipo' => [
            'in_list' => 'El tipo de sesión no es válido.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
