<?php

/**
 * @file    FormasPagoModel.php
 * @package App\Models
 *
 * Modelo para la tabla `formas_pago`.
 * Catálogo de métodos de pago aceptados por el estudio (efectivo, transferencia, etc.).
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Formas de Pago.
 *
 * Tabla: `formas_pago` (PK: id_form_pago).
 * Campos permitidos: nombre_forma_pago, tipo_pago.
 */
class FormasPagoModel extends Model
{
    protected $table            = 'formas_pago';
    protected $primaryKey       = 'id_form_pago';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre_forma_pago', 'tipo_pago'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Retorna todas las formas de pago ordenadas alfabéticamente.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        return $this->orderBy('nombre_forma_pago', 'ASC')->findAll();
    }
}
