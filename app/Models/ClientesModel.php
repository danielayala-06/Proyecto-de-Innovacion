<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientesModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id_cliente';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_persona',
        'red_social',
        'metodo_comunicacion',
        'acepta_promociones',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'id_persona' => 'required|is_natural_no_zero',
        'metodo_comunicacion' => 'required|in_list[whatsapp,llamada,correo,otro]',
        'acepta_promociones' => 'permit_empty|in_list[0,1]',
        'estado' => 'required|in_list[ACTIVO,INACTIVO]'
    ];
    protected $validationMessages = [
        'id_persona' => [
            'required' => 'La persona asociada es obligatoria.'
        ],
        'metodo_comunicacion' => [
            'required' => 'Debe seleccionar un método de comunicación.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
