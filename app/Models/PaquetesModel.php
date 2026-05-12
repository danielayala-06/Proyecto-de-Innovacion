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
        'categoria',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'nombre_paquete'   => 'required|max_length[150]',
        'nivel_disponible' => 'required|in_list[inicial-primaria,secundaria,postgrado,otro]',
        'precio'           => 'required|decimal|greater_than_equal_to[0]',
        'categoria'        => 'permit_empty|in_list[Cuadros,Anuarios,Paquetes,otros]',
        'estado'           => 'permit_empty|in_list[ACTIVO,INACTIVO]',
    ];
    protected $validationMessages = [
        'nombre_paquete'   => ['required' => 'El nombre del paquete es obligatorio.'],
        'nivel_disponible' => ['required' => 'El nivel es obligatorio.', 'in_list' => 'Nivel inválido.'],
        'precio'           => [
            'required'              => 'El precio es obligatorio.',
            'decimal'               => 'El precio debe ser numérico.',
            'greater_than_equal_to' => 'El precio no puede ser negativo.',
        ],
        'categoria'        => ['in_list' => 'Categoría inválida. Use: Cuadros, Anuarios, Paquetes, otros.'],
        'estado'           => ['in_list' => 'Estado inválido. Use: ACTIVO o INACTIVO.'],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
