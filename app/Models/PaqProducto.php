<?php

namespace App\Models;

use CodeIgniter\Model;

class PaqProducto extends Model
{
    protected $table            = 'paquetes_productos';
    protected $primaryKey       = 'id_paquete_prod';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_paquete',
        'id_producto',
        'cantidad',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
