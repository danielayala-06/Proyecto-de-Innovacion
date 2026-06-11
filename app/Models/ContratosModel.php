<?php

/**
 * @file    ContratosModel.php
 * @package App\Models
 *
 * Modelo para la tabla `contratos`.
 * Un contrato formaliza una cotización APROBADA, registra el adelanto inicial
 * y controla el ciclo de vida de los pagos (ACTIVO → COMPLETADO | CANCELADO).
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Contratos.
 *
 * Tabla: `contratos` (PK: id_contrato).
 * Relación: contratos.id_cotizacion → cotizaciones.id_cotizacion.
 * Estados válidos: ACTIVO | COMPLETADO | CANCELADO.
 */
class ContratosModel extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id_contrato';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cotizacion',
        'fecha_emision',
        'fecha_creacion',
        'adelanto',
        'total',
        'contacto2_nombre',
        'contacto2_telefono',
        'observaciones',
        'estado',
        'reglas_aplicadas',
        'archivado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_cotizacion' => 'required|is_natural_no_zero',
        'adelanto'      => 'required|decimal|greater_than[0]',
        'total'         => 'required|decimal|greater_than_equal_to[0]',
        'estado'        => 'required|in_list[ACTIVO,CANCELADO,COMPLETADO]',
    ];
    protected $validationMessages = [
        'id_cotizacion' => [
            'required' => 'La cotización asociada es obligatoria.',
        ],
        'adelanto' => [
            'required'              => 'El adelanto es obligatorio.',
            'greater_than'          => 'El adelanto debe ser mayor a cero.',
        ],
        'total' => [
            'required' => 'El total del contrato es obligatorio.',
        ],
        'estado' => [
            'in_list' => 'Estado inválido. Use: ACTIVO, CANCELADO o COMPLETADO.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista todos los contratos con datos del cliente y de la cotización vinculada.
     *
     * Filtros admitidos: estado (ACTIVO | COMPLETADO | CANCELADO).
     *
     * @param  array<string, mixed>     $filters
     * @return array<int, array<string, mixed>>
     */
    public function listarCompleto(array $filters = []): array
    {
        $q = $this
            ->select([
                'contratos.id_contrato', 'contratos.id_cotizacion',
                'contratos.fecha_creacion', 'contratos.fecha_emision',
                'contratos.adelanto', 'contratos.total',
                'contratos.estado', 'contratos.observaciones',
                "CONCAT(personas.nombres,' ',COALESCE(personas.apellidos,'')) AS cliente",
                'personas.telefono',
                'cotizaciones.total_estimado',
                'cotizaciones.estado AS estado_cotizacion',
                'colegios.nombre_colegio AS colegio',
                'pe.grado',
                'pe.seccion',
                'pe.num_estudiantes',
                "(SELECT MIN(sf.fecha_hora_sesion)
                   FROM sesiones_fotograficas sf
                   JOIN promociones_escolares pe2 ON pe2.id_promocion = sf.id_promocion
                   WHERE pe2.id_cotizacion = contratos.id_cotizacion
                     AND sf.estado != 'cancelado') AS primera_sesion",
            ])
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',    'personas.id_persona = clientes.id_persona')
            ->join('promociones_escolares pe', 'pe.id_cotizacion = contratos.id_cotizacion', 'left')
            ->join('colegios', 'colegios.id_colegio = pe.id_colegio', 'left')
            ->groupBy('contratos.id_contrato')
            ->orderBy('contratos.fecha_creacion', 'DESC');

        if (!empty($filters['estado'])) {
            $q->where('contratos.estado', strtoupper($filters['estado']));
        }

        return $q->findAll();
    }

    /**
     * Retorna el detalle de un contrato con los datos del cliente y la cotización.
     *
     * @param  int                       $id ID del contrato.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerConCliente(int $id): ?array
    {
        return $this
            ->select([
                'contratos.id_contrato', 'contratos.id_cotizacion',
                'contratos.fecha_creacion', 'contratos.fecha_emision',
                'contratos.adelanto', 'contratos.total',
                'contratos.estado', 'contratos.observaciones',
                'contratos.contacto2_nombre', 'contratos.contacto2_telefono',
                "CONCAT(personas.nombres,' ',COALESCE(personas.apellidos,'')) AS cliente",
                'personas.telefono', 'cotizaciones.total_estimado',
            ])
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas',    'personas.id_persona = clientes.id_persona')
            ->where('contratos.id_contrato', $id)
            ->first();
    }

    /**
     * Retorna todos los datos necesarios para generar el PDF del contrato.
     *
     * Estructura retornada:
     *   - Campos planos del contrato + cliente (nombres, telefono, total_estimado)
     *   - 'detalles'    → todos los ítems de cotizaciones_detalles con categoria resuelta
     *   - 'promociones' → todas las promociones_escolares con datos del colegio
     *
     * @param  int                       $id ID del contrato.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerDataPDFContrato(int $id): ?array
    {
        $contrato = $this
            ->select([
                'contratos.id_contrato',
                'contratos.id_cotizacion',
                'contratos.fecha_creacion',
                'contratos.adelanto',
                'contratos.total',
                'contratos.estado',
                'contratos.reglas_aplicadas',
                'contratos.contacto2_nombre',
                'contratos.contacto2_telefono',
                "CONCAT(p1.nombres,' ',COALESCE(p1.apellidos,'')) AS cliente",
                'p1.telefono',
                'cotizaciones.total_estimado',
                'cotizaciones.id_cliente2',
                "CONCAT(p2.nombres,' ',COALESCE(p2.apellidos,'')) AS cliente2_nombre",
                'p2.telefono AS cliente2_telefono',
                'p2.numero_documento AS cliente2_documento',
            ])
            ->join('cotizaciones', 'cotizaciones.id_cotizacion = contratos.id_cotizacion')
            ->join('clientes c1',  'c1.id_cliente = cotizaciones.id_cliente')
            ->join('personas p1',  'p1.id_persona = c1.id_persona')
            ->join('clientes c2',  'c2.id_cliente = cotizaciones.id_cliente2', 'left')
            ->join('personas p2',  'p2.id_persona = c2.id_persona', 'left')
            ->where('contratos.id_contrato', $id)
            ->first();

        if (!$contrato) {
            return null;
        }

        $contrato['reglas_aplicadas'] = !empty($contrato['reglas_aplicadas'])
            ? json_decode($contrato['reglas_aplicadas'], true)
            : ['violaciones' => [], 'activadas' => []];

        $idCot = (int) $contrato['id_cotizacion'];

        // Todos los ítems; LEFT JOIN a paquetes y productos para resolver la categoría visual.
        $contrato['detalles'] = $this->db
            ->table('cotizaciones_detalles cd')
            ->select([
                'cd.tipo_item',
                'cd.descripcion',
                'cd.cantidad',
                'cd.precio_unitario',
                'COALESCE(p.categoria, pr.categoria) AS categoria',
            ])
            ->join('paquetes p',    "p.id_paquete  = cd.id_referencia AND cd.tipo_item = 'paquete'",  'left')
            ->join('productos pr',  "pr.id_producto = cd.id_referencia AND cd.tipo_item = 'producto'", 'left')
            ->where('cd.id_cotizacion', $idCot)
            ->get()->getResultArray();

        // Promociones con datos del colegio (pueden ser varias por cotización).
        $contrato['promociones'] = $this->db
            ->table('promociones_escolares pe')
            ->select([
                'pe.nombre AS nombre_promocion',
                'pe.grado',
                'pe.seccion',
                'pe.num_estudiantes',
                'colegios.nombre_colegio',
                'colegios.distrito',
                'colegios.provincia',
            ])
            ->join('colegios', 'colegios.id_colegio = pe.id_colegio')
            ->where('pe.id_cotizacion', $idCot)
            ->get()->getResultArray();

        // Sesiones fotográficas vinculadas a la promoción de esta cotización.
        $contrato['sesiones'] = $this->db
            ->table('sesiones_fotograficas sf')
            ->select(['sf.fecha_hora_sesion', 'sf.tipo', 'sf.estado'])
            ->join('promociones_escolares pe', 'pe.id_promocion = sf.id_promocion')
            ->where('pe.id_cotizacion', $idCot)
            ->orderBy('sf.fecha_hora_sesion', 'ASC')
            ->limit(3)
            ->get()->getResultArray();

        return $contrato;
    }
}
