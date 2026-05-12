<?php

namespace App\Models;

use CodeIgniter\Model;

class PaquetesModel extends Model
{
    protected $table            = 'paquetes';
    protected $primaryKey       = 'id_paquete';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_paquete',
        'nivel_disponible',
        'descripcion',
        'imagen',
        'precio',
        'estado'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'nombre_paquete' => 'required|max_length[150]',
        'nivel_disponible' => 'required|in_list[inicial-primaria,secundaria,postgrado,otro]',
        'precio' => 'required|decimal|greater_than_equal_to[0]',
        'estado' => 'required|in_list[ACTIVO,INACTIVO]'
    ];
    protected $validationMessages = [
        'precio' => [
            'decimal' => 'El precio debe ser numérico.',
            'greater_than_equal_to' => 'El precio no puede ser negativo.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
