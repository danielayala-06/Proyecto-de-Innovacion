<?php

/**
 * @file    PaquetesProductosModel.php
 * @package App\Models
 *
 * Modelo para la tabla `paquetes_productos`.
 * Tabla de relación M:N entre paquetes y productos con campo de cantidad.
 * Permite que un mismo producto aparezca en distintos paquetes con cantidades diferentes.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Relación Paquete-Producto.
 *
 * Tabla: `paquetes_productos` (PK: id_paquete_prod).
 * Relaciones: id_paquete → paquetes, id_producto → productos.
 */
class PaquetesProductosModel extends Model
{
    protected $table            = 'paquetes_productos';
    protected $primaryKey       = 'id_paquete_prod';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'cantidad',
        'id_paquete',
        'id_producto',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_paquete'  => 'required|integer',
        'id_producto' => 'required|integer',
        'cantidad'    => 'required|integer|greater_than[0]',
    ];
    protected $validationMessages = [
        'id_paquete'  => ['required' => 'El paquete es obligatorio.'],
        'id_producto' => ['required' => 'El producto es obligatorio.'],
        'cantidad'    => [
            'required'     => 'La cantidad es obligatoria.',
            'greater_than' => 'La cantidad debe ser mayor a 0.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista los productos incluidos en un paquete con sus datos de catálogo.
     *
     * @param  int                      $idPaquete ID del paquete.
     * @return array<int, array<string, mixed>>
     */
    public function listarConProductos(int $idPaquete): array
    {
        return $this
            ->select('paquetes_productos.id_paquete_prod, paquetes_productos.cantidad,
                      productos.id_producto, productos.nombre_producto,
                      productos.categoria, productos.estado')
            ->join('productos', 'productos.id_producto = paquetes_productos.id_producto')
            ->where('paquetes_productos.id_paquete', $idPaquete)
            ->findAll();
    }

    /**
     * Retorna el conteo de productos por paquete para un batch de IDs.
     *
     * Utilizado por PaquetesModel::listarConConteo() para evitar N+1.
     *
     * @param  int[]                    $ids IDs de paquetes.
     * @return array<int, array<string, mixed>> Cada fila: { id_paquete, num_productos }.
     */
    public function contarPorPaquetes(array $ids): array
    {
        return $this
            ->select('id_paquete, COUNT(*) AS num_productos')
            ->whereIn('id_paquete', $ids)
            ->groupBy('id_paquete')
            ->findAll();
    }
}
