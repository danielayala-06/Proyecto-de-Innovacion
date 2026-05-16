<?php

/**
 * @file    CotizacionesDetallesModel.php
 * @package App\Models
 *
 * Modelo para la tabla `cotizaciones_detalles`.
 * Cada ítem de una cotización puede ser un paquete, un producto del catálogo
 * o un ítem personalizado (sin referencia a catálogo).
 * El subtotal no se almacena; se calcula como cantidad × precio_unitario.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Detalles de Cotización.
 *
 * Tabla: `cotizaciones_detalles` (PK: id_detalle).
 * Relación: id_cotizacion → cotizaciones.id_cotizacion.
 * Tipos de ítem: paquete | producto | personalizado.
 */
class CotizacionesDetallesModel extends Model
{
    protected $table            = 'cotizaciones_detalles';
    protected $primaryKey       = 'id_detalle';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cotizacion',
        'tipo_item',
        'id_referencia',
        'descripcion',
        'cantidad',
        'precio_unitario',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'tipo_item'       => 'required|in_list[paquete,producto,personalizado]',
        'cantidad'        => 'required|integer|greater_than[0]',
        'precio_unitario' => 'required|decimal|greater_than_equal_to[0]',
        'id_cotizacion'   => 'required|is_natural_no_zero',
        'id_referencia'   => 'permit_empty|is_natural_no_zero',
    ];
    protected $validationMessages = [
        'tipo_item' => [
            'required' => 'Debe indicar el tipo de item.',
            'in_list'  => 'Tipo de item inválido. Use: paquete, producto o personalizado.',
        ],
        'cantidad' => [
            'greater_than' => 'La cantidad debe ser mayor a cero.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Retorna solo los ítems de tipo 'paquete' de una cotización.
     *
     * Utilizado por SesionService para calcular límites de sesiones
     * a partir de los paquetes contratados.
     *
     * @param  int                      $idCotizacion ID de la cotización.
     * @return array<int, array<string, mixed>>
     */
    public function listarPaquetes(int $idCotizacion): array
    {
        return $this
            ->where('id_cotizacion', $idCotizacion)
            ->where('tipo_item', 'paquete')
            ->findAll();
    }
}
