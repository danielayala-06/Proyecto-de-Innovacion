<?php

/**
 * @file    ReglasPaquetesModel.php
 * @package App\Models
 *
 * Modelo para la tabla `reglas_paquetes`.
 * Define las bonificaciones que aplica un paquete según condiciones de cantidad.
 * Por ejemplo: si se contratan N o más paquetes, se agrega una sesión extra (tipo_beneficio='sesion_unica').
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Reglas de Paquetes.
 *
 * Tabla: `reglas_paquetes` (PK: id_regla).
 * Relación: id_paquete → paquetes.
 * Tipos de condición: CANTIDAD_MIN | CANTIDAD_MAX.
 * Tipos de beneficio: sesion_unica | descuento | etc.
 */
class ReglasPaquetesModel extends Model
{
    protected $table            = 'reglas_paquetes';
    protected $primaryKey       = 'id_regla';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_paquete',
        'descripcion',
        'tipo_condicion',
        'valor_condicion',
        'tipo_beneficio',
        'valor_beneficio',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Retorna las reglas de un paquete filtradas por tipo de beneficio.
     *
     * Utilizado por SesionService para obtener bonificaciones de tipo 'sesion_unica'
     * al calcular el límite de sesiones de una promoción.
     *
     * @param  int    $idPaquete      ID del paquete.
     * @param  string $tipoBeneficio  Tipo de beneficio a filtrar.
     * @return array<int, array<string, mixed>>
     */
    public function porPaqueteTipo(int $idPaquete, string $tipoBeneficio): array
    {
        return $this
            ->where('id_paquete', $idPaquete)
            ->where('tipo_beneficio', $tipoBeneficio)
            ->findAll();
    }
}
