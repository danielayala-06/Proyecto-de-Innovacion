<?php

namespace App\Models;

use CodeIgniter\Model;

class Cotizacion extends Model
{
    protected $table            = 'cotizaciones';
    protected $primaryKey       = 'id_cotizacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['num_dias_vigencia', 'fecha_registro', 'fecha_hora_inicio', 'fecha_hora_fin', 'direccion', 'referencia', 'latitud','estado', 'total_estimado', 'id_cliente', 'id_usuario'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
