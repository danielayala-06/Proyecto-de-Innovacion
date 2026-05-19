<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para la tabla `reglas_items`.
 * Tabla intermedia que enlaza reglas con paquetes o productos (many-to-many).
 *
 * Tabla: `reglas_items` (PK: id_item).
 * Relación: id_regla → reglas_paquetes (CASCADE delete).
 * tipo_item: 'paquete' | 'producto'.
 * id_referencia: ID del paquete o producto según tipo_item.
 */
class ReglasItemsModel extends Model
{
    protected $table            = 'reglas_items';
    protected $primaryKey       = 'id_item';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_regla',
        'tipo_item',
        'id_referencia',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $skipValidation         = false;
    protected $cleanValidationRules   = true;

    /**
     * Retorna todos los ítems (paquetes/productos) asociados a una regla.
     *
     * @param  int   $idRegla
     * @return array
     */
    public function porRegla(int $idRegla): array
    {
        return $this->where('id_regla', $idRegla)->findAll();
    }

    /**
     * Retorna todas las reglas (con sus items) asociadas a un paquete.
     *
     * @param  int   $idPaquete
     * @return array
     */
    public function idReglasPorPaquete(int $idPaquete): array
    {
        return $this->select('id_regla')
            ->where('tipo_item', 'paquete')
            ->where('id_referencia', $idPaquete)
            ->findAll();
    }
}
