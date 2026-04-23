<?php

namespace App\Models;

use CodeIgniter\Model;

class Cotizacion extends Model
{
    protected $table            = 'cotizaciones';
    protected $primaryKey       = 'id_cotizacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cliente',
        'id_usuario',
        'nombre_cotizacion',
        'num_dias_vigencia',
        'fecha_registro',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'direccion',
        'referencia',
        'latitud',
        'estado',
        'total_estimado',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getCotizacionesResumen(int $perPage = 10, int $page = 1)
    {
        $offset = ($page - 1) * $perPage;
        return $this->db->table('vista_cotizaciones_resumen')
        ->limit($perPage, $offset)
        ->get()
        ->getResultArray();
    }
}
