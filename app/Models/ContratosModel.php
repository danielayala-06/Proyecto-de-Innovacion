<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratosModel extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id_contrato';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cotizacion',
        'fecha_emision',
        'fecha_creacion',
        'adelanto',
        'total',
        'observaciones',
        'estado'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'id_cotizacion' => 'required|is_natural_no_zero',
        'adelanto' => 'required|decimal|greater_than_equal_to[0]',
        'total' => 'required|decimal|greater_than_equal_to[0]',
        'estado' => 'required|in_list[PENDIENTE,ACTIVO,FINALIZADO,CANCELADO]'
    ];
    protected $validationMessages = [
        'total' => [
            'required' => 'El total del contrato es obligatorio.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
