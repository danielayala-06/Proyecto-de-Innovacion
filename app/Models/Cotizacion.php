<?php

namespace App\Models;

use CodeIgniter\Model;

class Cotizacion extends Model
{
    protected $table            = 'cotizacion_pruebas';
    protected $primaryKey       = 'id_cotizacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['dni', 'cliente', 'paquete', 'created_at', 'updated_at', 'fecha_evento', 'monto_acordado','estado'];



    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
