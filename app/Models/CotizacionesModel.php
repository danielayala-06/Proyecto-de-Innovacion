<?php

namespace App\Models;

use CodeIgniter\Model;

class CotizacionesModel extends Model
{
    protected $table            = 'cotizaciones';
    protected $primaryKey       = 'id_cotizacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cliente',
        'id_usuario',
        'fecha_registro',
        'observaciones',
        'total_estimado',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'id_cliente' => 'required|is_natural_no_zero',
        'id_usuario' => 'required|is_natural_no_zero',
        'fecha_registro' => 'required|valid_date',
        'total_estimado' => 'required|decimal|greater_than_equal_to[0]',
        'estado' => 'required|in_list[PENDIENTE,APROBADA,RECHAZADA]'
    ];
    protected $validationMessages = [
        'id_cliente' => [
            'required' => 'El cliente es obligatorio.'
        ],
        'total_estimado' => [
            'decimal' => 'El total debe ser numérico.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
