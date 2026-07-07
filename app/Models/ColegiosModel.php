<?php

/**
 * @file    ColegiosModel.php
 * @package App\Models
 */

namespace App\Models;

use CodeIgniter\Model;

class ColegiosModel extends Model
{
    protected $table            = 'colegios';
    protected $primaryKey       = 'id_colegio';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_colegio',
        'distrito',
        'provincia',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $validationRules = [
        'nombre_colegio' => 'required|max_length[100]',
        'distrito'       => 'permit_empty|max_length[100]',
        'provincia'      => 'permit_empty|max_length[100]',
        'estado'         => 'permit_empty|in_list[ACTIVO,INACTIVO]',
    ];
    protected $validationMessages = [
        'nombre_colegio' => ['required' => 'El nombre del colegio es obligatorio.'],
        'estado'         => ['in_list'  => 'Estado inválido. Use: ACTIVO o INACTIVO.'],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Lista todos los colegios con resumen de su última promoción y contacto principal.
     * El contacto principal es la persona ligada a la cotización de la última promoción.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarConResumen(): array
    {
        return $this->db->query('
            SELECT
                c.id_colegio,
                c.nombre_colegio,
                c.distrito,
                c.provincia,
                c.estado,
                COUNT(DISTINCT pe_all.id_promocion) AS total_promociones,
                pe_last.nombre          AS ultima_promo_nombre,
                pe_last.grado           AS ultima_promo_grado,
                pe_last.seccion         AS ultima_promo_seccion,
                pe_last.anio            AS ultima_promo_anio,
                pe_last.num_estudiantes AS ultima_promo_estudiantes,
                per.nombres             AS contacto_nombres,
                per.apellidos           AS contacto_apellidos,
                per.telefono            AS contacto_telefono
            FROM colegios c
            LEFT JOIN promociones_escolares pe_all
                   ON pe_all.id_colegio = c.id_colegio
            LEFT JOIN (
                SELECT pe2.*
                FROM   promociones_escolares pe2
                INNER JOIN (
                    SELECT id_colegio, MAX(id_promocion) AS max_id
                    FROM   promociones_escolares
                    GROUP  BY id_colegio
                ) m ON m.id_colegio = pe2.id_colegio
                    AND m.max_id    = pe2.id_promocion
            ) pe_last ON pe_last.id_colegio = c.id_colegio
            LEFT JOIN cotizaciones cot ON cot.id_cotizacion = pe_last.id_cotizacion
            LEFT JOIN clientes     cl  ON cl.id_cliente     = cot.id_cliente
            LEFT JOIN personas     per ON per.id_persona    = cl.id_persona
            GROUP BY
                c.id_colegio, c.nombre_colegio, c.distrito, c.provincia, c.estado,
                pe_last.id_promocion, pe_last.nombre, pe_last.grado, pe_last.seccion,
                pe_last.anio, pe_last.num_estudiantes,
                per.nombres, per.apellidos, per.telefono
            ORDER BY c.nombre_colegio ASC
        ')->getResultArray();
    }

    /**
     * Retorna el detalle completo de un colegio: datos base, todas sus promociones
     * escolares (con estado de cotización y contrato) y los paquetes comprados
     * a lo largo de su historial.
     *
     * @param  int $id ID del colegio.
     * @return array<string, mixed>|null null si no existe.
     */
    public function detalle(int $id): ?array
    {
        $colegio = $this->find($id);
        if (!$colegio) {
            return null;
        }

        $colegio['promociones'] = $this->db->query('
            SELECT
                pe.id_promocion,
                pe.nombre,
                pe.grado,
                pe.seccion,
                pe.anio,
                pe.num_estudiantes,
                pe.is_active,
                cot.id_cotizacion,
                cot.estado        AS cot_estado,
                cot.total_estimado,
                con.id_contrato,
                con.estado        AS con_estado,
                per.nombres       AS contacto_nombres,
                per.apellidos     AS contacto_apellidos,
                per.telefono      AS contacto_telefono
            FROM   promociones_escolares pe
            LEFT JOIN cotizaciones cot ON cot.id_cotizacion = pe.id_cotizacion
            LEFT JOIN contratos    con ON con.id_cotizacion = pe.id_cotizacion
            LEFT JOIN clientes     cl  ON cl.id_cliente     = cot.id_cliente
            LEFT JOIN personas     per ON per.id_persona    = cl.id_persona
            WHERE  pe.id_colegio = ?
            ORDER  BY pe.anio DESC, pe.id_promocion DESC
        ', [$id])->getResultArray();

        $colegio['productos'] = $this->db->query('
            SELECT
                p.id_paquete,
                p.nombre_paquete,
                p.categoria,
                SUM(cd.cantidad)            AS cantidad_total,
                MIN(cd.precio_unitario)     AS precio_unitario,
                COUNT(DISTINCT pe.id_promocion) AS en_promociones,
                MAX(pe.anio)                AS ultimo_anio
            FROM   cotizaciones_detalles cd
            JOIN   cotizaciones          cot ON cot.id_cotizacion = cd.id_cotizacion
            JOIN   promociones_escolares pe  ON pe.id_cotizacion  = cot.id_cotizacion
            JOIN   paquetes              p   ON p.id_paquete      = cd.id_referencia
            WHERE  pe.id_colegio  = ?
              AND  cd.tipo_item   = \'paquete\'
            GROUP  BY p.id_paquete, p.nombre_paquete, p.categoria
            ORDER  BY en_promociones DESC, ultimo_anio DESC
        ', [$id])->getResultArray();

        return $colegio;
    }
}
