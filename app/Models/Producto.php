<?php

namespace App\Models;

use CodeIgniter\Model;

class Producto extends Model
{
    protected $table            = 'productos';
    protected $primaryKey       = 'id_producto';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_producto',
        'detalle',
        'tamanio',
        'unidad',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];


    // Validation
    protected $validationRules      = [
        'nombre_producto'   => 'required|max_length[150]|is_unique[productos.nombre_producto]|min_length[3]',
        'detalle'           => 'max_length[700]',
        'tamanio'           => 'max_length[50]|min_length[1]',
        'unidad'            => 'required|max_length[50]|min_length[3]',
        'estado'            => 'required|in_list[ACTIVO,INACTIVO]',
    ];
    protected $validationMessages   = [
        'nombre_producto'   => [
            'required' => 'El nombre del producto es requerido',
            'max_length' => 'Nombre de producto muy largo',
            'is_unique' => 'El nombre del producto ya existe',
            'min_length' => 'Nombre de producto debe tener al menos 3 caracteres',
        ],
        'detalle'           => [
            'max_length'=>'Detalle muy largo'
        ],
        'tamanio'            => [
            'max_length'=>'No se puede registrar el tamanio. El maximo de caracteres permitidos es: 50',
            'min_length'=>'No se pude registrar el tamanio. El minimo de caracteres es 3'
        ],
        'unidad'              => [
            'required'=>'La medidad del producto unidad es requerida',
            'max_length'=>'No se puede registrar la medida del producto unidad. Limite de caracteres permitidos es: 50',
            'min_length'=>'No se puede registrar la medida del producto unidad. El minimo de caracteres es 3'
        ],
        'estado'              => [
            'required'=>'El estado del producto es requerido',
            'in_list'=>'El estado del producto no es valido. Valores validos: ACTIVO, INACTIVO',
        ]

    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
