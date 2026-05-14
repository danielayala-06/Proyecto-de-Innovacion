<?php

namespace App\Models;

use CodeIgniter\Model;

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
        'id_producto'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'id_paquete'  => 'required|integer',
        'id_producto' => 'required|integer',
        'cantidad'    => 'required|integer|greater_than[0]',
    ];
    protected $validationMessages = [
        'id_paquete'  => ['required' => 'El paquete es obligatorio.'],
        'id_producto' => ['required' => 'El producto es obligatorio.'],
        'cantidad'    => ['required' => 'La cantidad es obligatoria.', 'greater_than' => 'La cantidad debe ser mayor a 0.'],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function listarConProductos(int $idPaquete): array
    {
        return $this
            ->select('paquetes_productos.id_paquete_prod, paquetes_productos.cantidad,
                      productos.id_producto, productos.nombre_producto,
                      productos.categoria, productos.tamanio, productos.estado')
            ->join('productos', 'productos.id_producto = paquetes_productos.id_producto')
            ->where('paquetes_productos.id_paquete', $idPaquete)
            ->findAll();
    }

    public function contarPorPaquetes(array $ids): array
    {
        return $this
            ->select('id_paquete, COUNT(*) AS num_productos')
            ->whereIn('id_paquete', $ids)
            ->groupBy('id_paquete')
            ->findAll();
    }
}
