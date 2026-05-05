<?php

namespace App\Models;

use CodeIgniter\Model;

class PaqServicio extends Model
{
    protected $table            = 'paquetes_servicios';
    protected $primaryKey       = 'id_paquete_serv';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_paquete',
        'id_servicio',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];
}
