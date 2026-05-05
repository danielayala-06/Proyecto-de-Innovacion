<?php

namespace App\Models;

use App\Database\Migrations\Servicios;
use CodeIgniter\Database\Database;
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
        $builder = $this->db->table('paquetes p');

        $builder->select([
            'p.id_paquete',
            'p.nombre_paquete',
            'p.categoria',
            'p.descripcion',
            'p.precio_base',
            'p.imagen',
            'p.estado',

            'pp.id_producto as prod_id',
            'pp.cantidad as prod_cantidad',
            'pr.nombre_producto',

            'ps.id_servicio as serv_id',
            'sv.nombre_servicio'
        ]);

        $builder->join('paquetes_productos pp',
                        'pp.id_paquete = p.id_paquete',
                        'left');
        $builder->join('productos pr', 'pr.id_producto = pp.id_producto', 'left');
        $builder->join('paquetes_servicios ps', 'ps.id_paquete = p.id_paquete', 'left');
        $builder->join('servicios sv', 'sv.id_servicio = ps.id_servicio', 'left');

        $rows = $builder->get()->getResultArray();

        // Si no hay registros
        if (empty($rows)) {
            return [];
        }

        $data = [];

        foreach ($rows as $row) {
            $id = $row['id_paquete'];

            // Inicializar paquete
            if (!isset($data[$id])) {
                $data[$id] = [
                    'paquete' => [
                        'id_paquete'  => $row['id_paquete'],
                        'nombre'      => $row['nombre_paquete'],
                        'categoria'   => $row['categoria'],
                        'descripcion' => $row['descripcion'],
                        'precio_base' => $row['precio_base'],
                        'imagen'      => $row['imagen'],
                        'estado'      => $row['estado'],
                    ],
                    'productos' => [],
                    'servicios' => []
                ];
            }

            // Productos
            if (!empty($row['prod_id'])) {
                $data[$id]['productos'][$row['prod_id']] = [
                    'id_producto' => $row['prod_id'],
                    'nombre'      => $row['nombre_producto'],
                    'cantidad'    => $row['prod_cantidad'],
                ];
            }

            // Servicios
            if (!empty($row['serv_id'])) {
                $data[$id]['servicios'][$row['serv_id']] = [
                    'id_servicio' => $row['serv_id'],
                    'nombre'      => $row['nombre_servicio'],
                ];
            }
        }

        // Reindexar
        $paquetes = [];

        foreach ($data as $item) {
            $paquetes[] = [
                'paquete'   => $item['paquete'],
                'productos' => array_values($item['productos']),
                'servicios' => array_values($item['servicios']),
            ];
        }


        return $paquetes;
    }

    public function paqueteFullById($id)
    {
        $builder = $this->db->table('paquetes p');

        $builder->select([
            'p.id_paquete',
            'p.nombre_paquete',
            'p.categoria',
            'p.descripcion',
            'p.precio_base',
            'p.imagen',
            'p.estado',

            'pp.id_producto as prod_id',
            'pp.cantidad as prod_cantidad',
            'pr.nombre_producto',

            'ps.id_servicio as serv_id',
            'sv.nombre_servicio'
        ]);

        $builder->join('paquetes_productos pp',
                        'pp.id_paquete = p.id_paquete',
                        'left');

        $builder->join('productos pr',
                        'pr.id_producto = pp.id_producto',
                        'left');

        $builder->join('paquetes_servicios ps',
                        'ps.id_paquete = p.id_paquete',
                        'left');

        $builder->join('servicios sv',
                        'sv.id_servicio = ps.id_servicio',
                        'left');

        $builder->where('p.id_paquete', $id);

        $rows = $builder->get()->getResultArray();

        if (empty($rows)) {
            return null;
        }

        $paquete = [
            'paquete' => [
                'id_paquete'  => $rows[0]['id_paquete'],
                'nombre'      => $rows[0]['nombre_paquete'],
                'categoria'   => $rows[0]['categoria'],
                'descripcion' => $rows[0]['descripcion'],
                'precio_base' => $rows[0]['precio_base'],
                'imagen'      => $rows[0]['imagen'],
                'estado'      => $rows[0]['estado'],
            ],
            'productos' => [],
            'servicios' => []
        ];

        foreach ($rows as $row) {

            if (!empty($row['prod_id'])) {
                $paquete['productos'][$row['prod_id']] = [
                    'id_producto' => $row['prod_id'],
                    'nombre'      => $row['nombre_producto'],
                    'cantidad'    => $row['prod_cantidad'],
                ];
            }

            if (!empty($row['serv_id'])) {
                $paquete['servicios'][$row['serv_id']] = [
                    'id_servicio' => $row['serv_id'],
                    'nombre'      => $row['nombre_servicio'],
                ];
            }
        }

        $paquete['productos'] = array_values($paquete['productos']);
        $paquete['servicios'] = array_values($paquete['servicios']);

        return $paquete;
    }
}
