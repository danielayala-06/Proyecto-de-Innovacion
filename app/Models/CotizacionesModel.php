<?php

namespace App\Models;

use CodeIgniter\Model;

class CotizacionesModel extends Model
{
    protected $table            = 'cotizaciones';
    protected $primaryKey       = 'id_cotizacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cliente',
        'id_usuario',
        'fecha_registro',
        'observaciones',
        'total_estimado',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation
    protected $validationRules = [
        'id_cliente' => 'required|is_natural_no_zero',
        'id_usuario' => 'required|is_natural_no_zero',
        'fecha_registro' => 'required|valid_date',
        'total_estimado' => 'required|decimal|greater_than_equal_to[0]',
        'estado' => 'required|in_list[PENDIENTE,APROBADA,RECHAZADA,EXPIRADA]'
    ];
    protected $validationMessages = [
        'id_cliente' => [
            'required' => 'El cliente es obligatorio.'
        ],
        'total_estimado' => [
            'decimal' => 'El total debe ser numérico.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function listarConCliente(): array
    {
        return $this
            ->select(['cotizaciones.*', 'clientes.id_cliente',
                      'personas.nombres', 'personas.apellidos', 'usuarios.nombre_user'])
            ->join('clientes', 'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas', 'personas.id_persona = clientes.id_persona')
            ->join('usuarios', 'usuarios.id_usuario = cotizaciones.id_usuario')
            ->paginate();
    }

    // Cotizaciones APROBADAS que aún no tienen contrato activo (no CANCELADO)
    public function listarAprobadasSinContrato(): array
    {
        return $this
            ->select(['cotizaciones.*', 'clientes.id_cliente',
                      'personas.nombres', 'personas.apellidos', 'usuarios.nombre_user'])
            ->join('clientes',  'clientes.id_cliente  = cotizaciones.id_cliente')
            ->join('personas',  'personas.id_persona  = clientes.id_persona')
            ->join('usuarios',  'usuarios.id_usuario  = cotizaciones.id_usuario')
            ->join('contratos', "contratos.id_cotizacion = cotizaciones.id_cotizacion AND contratos.estado != 'CANCELADO'", 'left')
            ->where('cotizaciones.estado', 'APROBADA')
            ->where('contratos.id_contrato IS NULL', null, false)
            ->findAll();
    }

    // Marca como EXPIRADA toda cotización APROBADA con más de $dias días sin contrato.
    // Retorna el número de filas afectadas.
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

    // Retorna el subconjunto de IDs que ya tienen un contrato no cancelado.
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

    // Verifica y expira una cotización específica si corresponde.
    // Se usa al consultar el detalle para que el estado siempre esté actualizado.
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

    public function obtenerConCliente(int $id): ?array
    {
        return $this
            ->select(['cotizaciones.*', 'clientes.id_cliente',
                      'personas.nombres', 'personas.apellidos', 'usuarios.nombre_user'])
            ->join('clientes', 'clientes.id_cliente = cotizaciones.id_cliente')
            ->join('personas', 'personas.id_persona = clientes.id_persona')
            ->join('usuarios', 'usuarios.id_usuario = cotizaciones.id_usuario')
            ->find($id);
    }
}
