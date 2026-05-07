<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductosModel extends Model
{
    protected $table            = 'productos';
    protected $primaryKey       = 'id_producto';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_producto',
        'detalle',
        'categoria',
        'tamanio',
        'estado'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'nombre_producto' => 'required|max_length[150]',
        'categoria' => 'required|in_list[cuadro,anuario,photobook,otro]',
        'estado' => 'required|in_list[ACTIVO,INACTIVO]'
    ];
    protected $validationMessages = [
        'nombre_producto' => [
            'required' => 'El nombre del producto es obligatorio.'
        ],
        'categoria' => [
            'in_list' => 'La categoría seleccionada no es válida.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

}
