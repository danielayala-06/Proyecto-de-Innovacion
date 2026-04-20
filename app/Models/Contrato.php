<?php

namespace App\Models;

use CodeIgniter\Model;

class Contrato extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id_contrato';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cotizacion',
        'fecha_contrato',
        'fecha_emision',
        'adelanto',
        'observaciones',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'estado',
        'total_final',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id_cotizacion' => 'int',
        'fecha_contrato' => 'string',
        'fecha_emision' => 'string',
        'fecha_hora_incio' => 'datetime',
        'fecha_hora_fin' => 'datetime',
        'total_final' => 'string',
    ];
    protected array $castHandlers = [];


    // Validation
    protected $validationRules      = [
        'id_cotizacion' => 'required|natural_no_zero|max_length[11]',
        'fecha_contrato' => 'required|valid_date',
        'fecha_emision' => 'required|valid_date',
        'fecha_hora_incio' => 'required|valid_date',
        'fecha_hora_fin' => 'required|valid_date',
        'total_final' => 'required|numeric|max_length[11]|is_natural_no_zero',
    ];
    protected $validationMessages   = [
        'id_cotizacion' => [
            'required'=>'Se requiere el campo id_cotizacion',
            'natural_no_zero'=>'Ingrese un numero de cotizacion valido',
            'max_length'=>'Numero de cotizacion muy largo',
        ],
        'fecha_contrato' => [
            'required'=>'Se requiere la fecha del contrato',
            'valid_date'=>'Ingrese una fecha valida',
        ],
        'fecha_emision' => [
            'required'=>'Se requiere la fecha emision',
            'valid_date'=>'Ingrese una fecha valida',
        ],
        'fecha_hora_inicio' => [
            'required'=>'Se requiere la fecha de inicio del evento/sesion',
            'valid_date'=>'Ingrese una fecha valida',
        ],
        'fecha_hora_fin' => [
            'required'=>'Se requiere la fecha final del evento/sesion',
            'valid_date'=>'Ingrese una fecha valida',
        ],
        'total_final' => [
            'required'=>'Se require el valor total del contrato',
            'numeric'=>'Monto de contrato invalido',
            'max_length'=>'Contrato demasiado caro',
            'is_natural_no_zero'=>'Monto de contrato invalido',
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
