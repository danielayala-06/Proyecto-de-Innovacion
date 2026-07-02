<?php

/**
 * @file    CotizacionesModel.php
 * @package App\Models
 *
 * Modelo para la tabla `cotizaciones`.
 * Gestiona el ciclo de vida de las cotizaciones: PENDIENTE → APROBADA → EXPIRADA
 * (si supera los 30 días sin convertirse en contrato) o → RECHAZADA.
 * Incluye métodos para la expiración masiva y puntual sin N+1.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Cotizaciones.
 *
 * Tabla: `cotizaciones` (PK: id_cotizacion).
 * Estados: PENDIENTE | APROBADA | RECHAZADA | EXPIRADA.
 * Relaciones: id_cliente → clientes, id_usuario → usuarios.
 */
class CotizacionesModel extends Model
{
    protected $table            = 'cotizaciones';
    protected $primaryKey       = 'id_cotizacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cliente',
        'id_cliente2',
        'id_usuario',
        'fecha_registro',
        'observaciones',
        'total_estimado',
        'descuento_monto',
        'estado',
        'archivado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'id_cliente'     => 'required|is_natural_no_zero',
        'id_usuario'     => 'required|is_natural_no_zero',
        'fecha_registro' => 'required|valid_date',
        'total_estimado' => 'required|decimal|greater_than_equal_to[0]',
        'estado'         => 'required|in_list[PENDIENTE,APROBADA,RECHAZADA,EXPIRADA]',
    ];
    protected $validationMessages = [
        'id_cliente' => [
            'required' => 'El cliente es obligatorio.',
        ],
        'total_estimado' => [
            'decimal' => 'El total debe ser numérico.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista todas las cotizaciones con datos del cliente y el usuario que las creó.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarConCliente(): array
    {
        return $this
            ->select([
                'cotizaciones.*',
                'clientes.id_cliente',
                'p1.nombres', 'p1.apellidos',
                'usuarios.nombre_user',
                'c2.id_cliente AS id_cliente2',
                'p2.nombres AS nombres2', 'p2.apellidos AS apellidos2',
                'p2.telefono AS telefono2',
                'pe.id_promocion AS id_promocion_fk',
                'pe.nombre      AS promo_nombre',
                'pe.num_estudiantes',
                'co.id_colegio  AS id_colegio_fk',
                'co.nombre_colegio',
            ])
            ->join('clientes',                  'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas p1',               'p1.id_persona = clientes.id_persona')
            ->join('usuarios',                  'usuarios.id_usuario = cotizaciones.id_usuario')
            ->join('clientes c2',               'c2.id_cliente = cotizaciones.id_cliente2', 'left')
            ->join('personas p2',               'p2.id_persona = c2.id_persona', 'left')
            ->join('promociones_escolares pe',  'pe.id_cotizacion = cotizaciones.id_cotizacion', 'left')
            ->join('colegios co',               'co.id_colegio = pe.id_colegio', 'left')
            ->orderBy('cotizaciones.id_cotizacion', 'DESC')
            ->findAll();
    }

    /**
     * Retorna el detalle de una cotización con los datos del cliente y el usuario.
     *
     * @param  int                       $id ID de la cotización.
     * @return array<string, mixed>|null     null si no existe.
     */
    public function obtenerConCliente(int $id): ?array
    {
        return $this
            ->select([
                'cotizaciones.*',
                'clientes.id_cliente',
                'p1.nombres', 'p1.apellidos',
                'p1.tipo_documento', 'p1.numero_documento',
                'p1.telefono', 'p1.correo',
                'usuarios.nombre_user',
                'c2.id_cliente AS id_cliente2',
                'p2.nombres AS nombres2', 'p2.apellidos AS apellidos2',
                'p2.tipo_documento AS tipo_documento2', 'p2.numero_documento AS numero_documento2',
                'p2.telefono AS telefono2', 'p2.correo AS correo2',
            ])
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas p1', 'p1.id_persona = clientes.id_persona')
            ->join('usuarios',    'usuarios.id_usuario = cotizaciones.id_usuario')
            ->join('clientes c2', 'c2.id_cliente = cotizaciones.id_cliente2', 'left')
            ->join('personas p2', 'p2.id_persona = c2.id_persona', 'left')
            ->find($id);
    }

    /**
     * Lista cotizaciones APROBADAS que no tienen aún un contrato activo (no CANCELADO).
     *
     * Utilizado por el selector de cotizaciones al generar contratos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarAprobadasSinContrato(): array
    {
        return $this
            ->select([
                'cotizaciones.*',
                'clientes.id_cliente',
                'p1.nombres', 'p1.apellidos',
                'usuarios.nombre_user',
                'c2.id_cliente AS id_cliente2',
                'p2.nombres AS nombres2', 'p2.apellidos AS apellidos2',
            ])
            ->join('clientes',    'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas p1', 'p1.id_persona = clientes.id_persona')
            ->join('usuarios',    'usuarios.id_usuario = cotizaciones.id_usuario')
            ->join('clientes c2', 'c2.id_cliente = cotizaciones.id_cliente2', 'left')
            ->join('personas p2', 'p2.id_persona = c2.id_persona', 'left')
            ->join('contratos',   "contratos.id_cotizacion = cotizaciones.id_cotizacion AND contratos.estado != 'CANCELADO'", 'left')
            ->where('cotizaciones.estado', 'APROBADA')
            ->where('contratos.id_contrato IS NULL', null, false)
            ->findAll();
    }

    /**
     * Marca como EXPIRADAS todas las cotizaciones APROBADAS con más de $dias días
     * que no tienen un contrato activo.
     *
     * Se ejecuta vía raw SQL para evitar que el modelo valide el ENUM antes
     * de que la migración lo haya extendido. Retorna el número de filas afectadas.
     *
     * @param  int $dias Días de antigüedad a partir de los cuales se expira (default: 30).
     * @return int       Número de cotizaciones expiradas.
     */
    public function expirarAntiguas(int $dias = 30): int
    {
        $corte = date('Y-m-d', strtotime("-{$dias} days"));

        $this->db->query(
            "UPDATE cotizaciones
             SET estado = 'EXPIRADA'
             WHERE estado = 'APROBADA'
               AND DATE(fecha_registro) < ?
               AND id_cotizacion NOT IN (
                   SELECT id_cotizacion FROM contratos WHERE estado != 'CANCELADO'
               )",
            [$corte]
        );

        return $this->db->affectedRows();
    }

    /**
     * Verifica y expira una cotización específica si supera el límite de días.
     *
     * Llamado desde el servicio al consultar el detalle de una cotización,
     * para garantizar que el estado esté actualizado sin necesidad de listar primero.
     *
     * @param  int $id   ID de la cotización a verificar.
     * @param  int $dias Días de antigüedad límite (default: 30).
     * @return void
     */
    public function verificarExpiracion(int $id, int $dias = 30): void
    {
        $corte = date('Y-m-d', strtotime("-{$dias} days"));

        $this->db->query(
            "UPDATE cotizaciones
             SET estado = 'EXPIRADA'
             WHERE id_cotizacion = ?
               AND estado = 'APROBADA'
               AND DATE(fecha_registro) < ?
               AND id_cotizacion NOT IN (
                   SELECT id_cotizacion FROM contratos WHERE estado != 'CANCELADO'
               )",
            [$id, $corte]
        );
    }

    /**
     * Retorna el subconjunto de IDs de cotizaciones que ya tienen un contrato no cancelado.
     *
     * Permite al servicio marcar la bandera `tiene_contrato` en un solo batch query
     * en lugar de una consulta por cotización (anti-N+1).
     *
     * @param  int[] $ids IDs de cotizaciones a verificar.
     * @return int[]      IDs que tienen contrato activo o completado.
     */
    public function idsCotizacionesConContrato(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $rows = $this->db->table('contratos')
            ->select('id_cotizacion')
            ->whereIn('id_cotizacion', $ids)
            ->where('estado !=', 'CANCELADO')
            ->get()
            ->getResultArray();

        return array_column($rows, 'id_cotizacion');
    }
}
