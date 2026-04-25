<?php

namespace App\Models;

use CodeIgniter\Model;

class Paquete extends Model
{
    
    protected $table            = 'paquetes';
    protected $primaryKey       = 'id_paquete';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_paquete',
        'precio_base',
        'imagen',
        'descripcion',
        'estado'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
