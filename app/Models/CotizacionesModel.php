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
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
