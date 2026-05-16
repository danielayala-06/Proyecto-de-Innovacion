<?php

/**
 * @file    SesionesFotograficasModel.php
 * @package App\Models
 *
 * Modelo para la tabla `sesiones_fotograficas`.
 * Una sesión representa un evento fotográfico programado para una promoción.
 * El límite de sesiones por tipo está determinado por el paquete contratado.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Sesiones Fotográficas.
 *
 * Tabla: `sesiones_fotograficas` (PK: id_sesion).
 * Relación: id_promocion → promociones_escolares.
 * Tipos: exteriores | colegio | estudio | otro.
 * Estados: pendiente | finalizado | cancelado.
 */
class SesionesFotograficasModel extends Model
{
    protected $table            = 'sesiones_fotograficas';
    protected $primaryKey       = 'id_sesion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_promocion',
        'fecha_hora_sesion',
        'tipo',
        'observaciones',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_promocion'      => 'required|is_natural_no_zero',
        'fecha_hora_sesion' => 'required|valid_date[Y-m-d H:i:s]',
        'tipo'              => 'required|in_list[exteriores,colegio,estudio,otro]',
        'estado'            => 'required|in_list[pendiente,finalizado,cancelado]',
    ];
    protected $validationMessages = [
        'id_promocion' => [
            'required' => 'La promoción es obligatoria.',
        ],
        'fecha_hora_sesion' => [
            'required'   => 'La fecha y hora de la sesión son obligatorias.',
            'valid_date' => 'El formato debe ser YYYY-MM-DD HH:MM:SS.',
        ],
        'tipo' => [
            'in_list' => 'Tipo inválido. Use: exteriores, colegio, estudio u otro.',
        ],
        'estado' => [
            'in_list' => 'Estado inválido. Use: pendiente, finalizado o cancelado.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista las sesiones de una promoción ordenadas cronológicamente.
     *
     * @param  int                      $idPromocion ID de la promoción.
     * @return array<int, array<string, mixed>>
     */
    public function listarPorPromocion(int $idPromocion): array
    {
        return $this
            ->where('id_promocion', $idPromocion)
            ->orderBy('fecha_hora_sesion', 'ASC')
            ->findAll();
    }
}
