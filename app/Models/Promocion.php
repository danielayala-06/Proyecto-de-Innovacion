<?php

namespace App\Models;

use CodeIgniter\Model;

class Promocion extends Model
{
    protected $table            = 'promocions';
    protected $primaryKey       = 'id_promocion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_promocion',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'descuento',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'fecha_inicio'=>'date',
        'fecha_fin'=>'date',
        'descuento'=>'string',
    ];
    protected array $castHandlers = [];


    // Validation
    protected $validationRules      = [
        'nombre_promocion'  =>'required|max_length[150]|is_unique[promocions.nombre_promocion]|min_length[5]',
        'tipo'              =>'required|min_length[3]|max_length[50]',
        'fecha_inicio'      =>'valid_date|required',
        'fecha_fin'         =>'valid_date|required',
        'descuento'         =>'numeric',
        'estado'            =>'required|in_list[ACTIVO,INACTIVO,VENCIDO]',
    ];
    protected $validationMessages   = [
        'nombre_promocion'=>[
            'required'=>'El nombre de la promocion es requerido',
            'max_length'=>'El nombre de la promocion supero el limite de caracteres',
            'min_length'=>'El nombre de la promocion no es valido',
            'is_unique'=>'Una promocion con ese nombre ya se encuentra registrado',
        ],
        'tipo'=>[
            'required'=>'El tipo del producto es requerido',
            'min_length'=>'El tipo del producto no es valido',
            'max_length'=>'El tipo del producto no es valido',
        ],
        'fecha_inicio'=>[
            'required'=>'La fecha de inicio de la promocion es requerido',
            'valid_date'=>'La fecha de inicio de la promocion no es valida',
        ],
        'fecha_fin'=>[
            'required'=>'La fecha final de la promocion es requerido',
            'valid_date'=>'La fecha final de la promocion no es valida',
        ],
        'descuento'=>[
            'numeric'=>'El descuento no es valido',
        ],
        'estado'=>[
            'required'=>'El estado de la promocin es requerido',
            'in_list'=>'El estado de la promocion no es valido',
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
