<?php

/**
 * @file    ProductosModel.php
 * @package App\Models
 *
 * Modelo para la tabla `productos`.
 * Los productos son los artículos individuales del catálogo fotográfico
 * (cuadros, anuarios, photobooks, etc.) que pueden agruparse en paquetes.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Productos.
 *
 * Tabla: `productos` (PK: id_producto).
 * Categorías: cuadro | anuario | photobook | otro.
 * Estados: ACTIVO | INACTIVO.
 */
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
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'nombre_producto' => 'required|max_length[150]',
        'categoria'       => 'required|in_list[cuadro,anuario,photobook,otro]',
        'estado'          => 'required|in_list[ACTIVO,INACTIVO]',
    ];
    protected $validationMessages = [
        'nombre_producto' => [
            'required' => 'El nombre del producto es obligatorio.',
        ],
        'categoria' => [
            'in_list' => 'La categoría seleccionada no es válida.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
