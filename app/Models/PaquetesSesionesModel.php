<?php

/**
 * @file    PaquetesSesionesModel.php
 * @package App\Models
 *
 * Modelo para la tabla `paquetes_sesiones`.
 * Define el número de sesiones fotográficas de cada tipo incluidas en un paquete.
 * Utilizado por SesionService para calcular cuántas sesiones puede crear una promoción.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Sesiones por Paquete.
 *
 * Tabla: `paquetes_sesiones` (PK: id_paquete_sesion).
 * Relación: id_paquete → paquetes.
 * Tipos de sesión: exteriores | colegio | estudio | otro.
 */
class PaquetesSesionesModel extends Model
{
    protected $table            = 'paquetes_sesiones';
    protected $primaryKey       = 'id_paquete_sesion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_paquete',
        'tipo_sesion',
        'lugar_descripcion',
        'num_sesiones',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_paquete'   => 'required|is_natural_no_zero',
        'tipo_sesion'  => 'required|in_list[exteriores,colegio,estudio,otro]',
        'num_sesiones' => 'required|is_natural_no_zero',
    ];
    protected $validationMessages = [
        'tipo_sesion' => [
            'in_list' => 'Tipo inválido. Use: exteriores, colegio, estudio u otro.',
        ],
        'num_sesiones' => [
            'is_natural_no_zero' => 'El número de sesiones debe ser al menos 1.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Retorna las configuraciones de sesión de un tipo dado para un batch de paquetes.
     *
     * Utilizado por SesionService::calcularLimite() para obtener en una sola consulta
     * cuántas sesiones de un tipo están incluidas en cada paquete contratado.
     *
     * @param  int[]   $idsPaquetes IDs de paquetes a consultar.
     * @param  string  $tipo        Tipo de sesión (exteriores, colegio, estudio, otro).
     * @return array<int, array<string, mixed>>
     */
    public function configuracionesPorTipo(array $idsPaquetes, string $tipo): array
    {
        return $this
            ->whereIn('id_paquete', $idsPaquetes)
            ->where('tipo_sesion', $tipo)
            ->findAll();
    }
}
