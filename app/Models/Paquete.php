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
        'categoria',
        'descripcion',
        'estado'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'nombre_paquete' => 'required|min_length[3]|max_length[150]',
        'categoria'      => 'required|in_list[CUADROS,ANUARIOS,QUINOS,SESIONES,OTROS]',
        'precio_base'    => 'required|numeric|greater_than_equal_to[0]|regex_match[/^\d{1,8}(\.\d{1,2})?$/]',
        'imagen'         => 'permit_empty|min_length[3]|max_length[255]',
        'estado'         => 'required|in_list[ACTIVO,INACTIVO]',
    ];

    protected $validationMessages = [

        'nombre_paquete' => [
            'required'   => 'El nombre del paquete es obligatorio.',
            'min_length' => 'El nombre del paquete debe tener al menos 3 caracteres.',
            'max_length' => 'El nombre del paquete no puede superar los 150 caracteres.',
        ],

        'categoria' => [
            'required' => 'Debe seleccionar una categoría para el paquete.',
            'in_list'  => 'La categoría seleccionada no es válida.',
        ],

        'precio_base' => [
            'required'               => 'El precio base es obligatorio.',
            'numeric'               => 'El precio debe ser un número válido.',
            'greater_than_equal_to' => 'El precio no puede ser negativo.',
            'regex_match'           => 'El precio debe tener hasta 8 enteros y 2 decimales (ej: 12345678.90).',
        ],

        'imagen' => [
            'min_length' => 'La ruta de la imagen debe tener al menos 3 caracteres.',
            'max_length' => 'La ruta de la imagen no puede superar los 255 caracteres.',
        ],

        'estado' => [
            'required' => 'El estado es obligatorio.',
            'in_list'  => 'El estado debe ser ACTIVO o INACTIVO.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function paquetesFull()
    {

    }
}
