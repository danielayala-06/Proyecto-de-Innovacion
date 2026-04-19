<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ViewsFull extends Migration
{
    public function up()
    {
        // ------------------------------------------------------------
        // v_paquetes_completo
        // Paquete con todos sus servicios y productos concatenados
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_paquetes_completo");
        $this->db->query("
            CREATE VIEW v_paquetes_completo AS
            SELECT
                p.id_paquete,
                p.nombre_paquete,
                p.categoria,
                p.imagen,
                p.descripcion,
                p.precio_base,
                p.estado,
                GROUP_CONCAT(DISTINCT s.nombre_servicio ORDER BY s.nombre_servicio SEPARATOR ' | ') AS servicios,
                GROUP_CONCAT(DISTINCT CONCAT(pp.cantidad, 'x ', pr.nombre_producto) ORDER BY pr.nombre_producto SEPARATOR ' | ') AS productos,
                COUNT(DISTINCT ps.id_servicio) AS total_servicios,
                COUNT(DISTINCT pp.id_producto) AS total_productos,
                COUNT(DISTINCT rp.id_regla)    AS total_reglas
            FROM paquetes p
            LEFT JOIN paquetes_servicios ps ON ps.id_paquete  = p.id_paquete
            LEFT JOIN servicios          s  ON s.id_servicio  = ps.id_servicio
            LEFT JOIN paquetes_productos pp ON pp.id_paquete  = p.id_paquete
            LEFT JOIN productos          pr ON pr.id_producto = pp.id_producto
            LEFT JOIN reglas_paquetes     rp ON rp.id_paquete  = p.id_paquete
            GROUP BY
                p.id_paquete, p.nombre_paquete, p.categoria,
                p.imagen, p.descripcion, p.precio_base, p.estado
        ");

        // ------------------------------------------------------------
        // v_clientes_completo
        // Cliente con datos de persona y empresa representada
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_clientes_completo");
        $this->db->query("
            CREATE VIEW v_clientes_completo AS
            SELECT
                c.id_cliente,
                c.tipo_cliente,
                c.estado,
                c.red_social,
                c.metodo_comunicacion,
                p.id_persona,
                p.nombres,
                p.apellidos,
                CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo,
                p.telefono,
                p.tel_alternativo,
                p.correo,
                p.tipo_documento,
                p.numero_documento,
                e.id_empresa,
                e.razon_social,
                e.ruc,
                e.nombre_comercial,
                e.telefono      AS telefono_empresa,
                e.correo        AS correo_empresa
            FROM clientes  c
            INNER JOIN personas p ON p.id_persona = c.id_persona
            LEFT  JOIN empresas e ON e.id_empresa = c.id_empresa
        ");

        // ------------------------------------------------------------
        // v_promociones_completo
        // Promoción con todos los ítems a los que aplica
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_promociones_completo");
        $this->db->query("
            CREATE VIEW v_promociones_completo AS
            SELECT
                pr.id_promocion,
                pr.nombre_promocion,
                pr.tipo,
                pr.fecha_inicio,
                pr.fecha_fin,
                pr.descuento,
                pr.estado,
                DATEDIFF(pr.fecha_fin, CURRENT_DATE)                              AS dias_restantes,
                GROUP_CONCAT(DISTINCT pa.nombre_paquete  ORDER BY pa.nombre_paquete  SEPARATOR ' | ') AS paquetes_aplicados,
                GROUP_CONCAT(DISTINCT s.nombre_servicio  ORDER BY s.nombre_servicio  SEPARATOR ' | ') AS servicios_aplicados,
                GROUP_CONCAT(DISTINCT p.nombre_producto  ORDER BY p.nombre_producto  SEPARATOR ' | ') AS productos_aplicados,
                COUNT(DISTINCT pp.id_paquete)  AS total_paquetes,
                COUNT(DISTINCT ps.id_servicio) AS total_servicios,
                COUNT(DISTINCT ppd.id_producto) AS total_productos
            FROM promociones pr
            LEFT JOIN promociones_paquetes  pp  ON pp.id_promocion  = pr.id_promocion
            LEFT JOIN paquetes           pa  ON pa.id_paquete    = pp.id_paquete
            LEFT JOIN promociones_servicios ps  ON ps.id_promocion  = pr.id_promocion
            LEFT JOIN servicios          s   ON s.id_servicio    = ps.id_servicio
            LEFT JOIN promociones_productos ppd ON ppd.id_promocion = pr.id_promocion
            LEFT JOIN productos          p   ON p.id_producto    = ppd.id_producto
            GROUP BY
                pr.id_promocion, pr.nombre_promocion, pr.tipo,
                pr.fecha_inicio, pr.fecha_fin, pr.descuento, pr.estado
        ");

        // ------------------------------------------------------------
        // v_cotizaciones_completo
        // Cotización con datos del cliente, usuario y totales por tipo
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_cotizaciones_completo");
        $this->db->query("
            CREATE VIEW v_cotizaciones_completo AS
            SELECT
                cot.id_cotizacion,
                cot.nombre_cotizacion,
                cot.estado,
                cot.fecha_registro,
                cot.fecha_hora_inicio,
                cot.fecha_hora_fin,
                cot.num_dias_vigencia,
                DATE_ADD(cot.fecha_registro, INTERVAL cot.num_dias_vigencia DAY) AS fecha_expiracion,
                cot.direccion,
                cot.referencia,
                cot.observaciones,
                cot.total_estimado,
                -- Cliente
                cl.id_cliente,
                CONCAT(p.nombres, ' ', p.apellidos) AS nombre_cliente,
                p.telefono                          AS telefono_cliente,
                p.correo                            AS correo_cliente,
                cl.tipo_cliente,
                e.razon_social                      AS empresa_cliente,
                -- Usuario que gestiona
                u.id_usuario,
                CONCAT(pu.nombres, ' ', pu.apellidos) AS nombre_usuario,
                -- Paquetes
                
                -- Agregados de paquetes
                COUNT(cp.id_paquete) AS cantidad_paquetes,
                COALESCE(SUM(pa.precio_base), 0) AS total_paquetes,
                
                -- Totales por tipo de ítem
                COALESCE(cp.subtotal_paquetes,  0) AS subtotal_paquetes,
                COALESCE(cs.subtotal_servicios, 0) AS subtotal_servicios,
                COALESCE(cpd.subtotal_productos, 0) AS subtotal_productos
            FROM cotizaciones cot
            INNER JOIN clientes  cl  ON cl.id_cliente = cot.id_cliente
            INNER JOIN personas  p   ON p.id_persona  = cl.id_persona
            INNER JOIN usuarios  u   ON u.id_usuario  = cot.id_usuario
            INNER JOIN personas  pu  ON pu.id_persona = u.id_persona
            LEFT  JOIN empresas  e   ON e.id_empresa  = cl.id_empresa
            LEFT  JOIN (
                SELECT id_cotizacion, SUM(subtotal) AS subtotal_paquetes
                FROM cotizaciones_paquetes GROUP BY id_cotizacion
            ) cp  ON cp.id_cotizacion  = cot.id_cotizacion
            LEFT  JOIN (
                SELECT id_cotizacion, SUM(subtotal) AS subtotal_servicios
                FROM cotizaciones_servicios GROUP BY id_cotizacion
            ) cs  ON cs.id_cotizacion  = cot.id_cotizacion
            LEFT  JOIN (
                SELECT id_cotizacion, SUM(subtotal) AS subtotal_productos
                FROM cotizaciones_productos GROUP BY id_cotizacion
            ) cpd ON cpd.id_cotizacion = cot.id_cotizacion
        ");

        // ------------------------------------------------------------
        // v_contratos_completo
        // Contrato con datos de cotización, cliente y saldo pendiente
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_contratos_completo");
        $this->db->query("
            CREATE VIEW v_contratos_completo AS
            SELECT
                con.id_contrato,
                con.estado,
                con.fecha_contrato,
                con.fecha_emision,
                con.fecha_hora_inicio,
                con.fecha_hora_fin,
                con.adelanto,
                con.total_final,
                con.observaciones,
                -- Saldo pendiente en tiempo real
                COALESCE(pg.total_pagado, 0)                                          AS total_pagado,
                con.total_final - con.adelanto - COALESCE(pg.total_pagado, 0)         AS saldo_pendiente,
                -- Cotización origen
                cot.id_cotizacion,
                cot.nombre_cotizacion,
                cot.total_estimado,
                -- Cliente
                cl.id_cliente,
                CONCAT(p.nombres, ' ', p.apellidos) AS nombre_cliente,
                p.telefono                          AS telefono_cliente,
                p.correo                            AS correo_cliente,
                cl.tipo_cliente,
                e.razon_social                      AS empresa_cliente,
                -- Reprogramaciones
                COALESCE(rp.total_reprogramaciones, 0) AS total_reprogramaciones,
                rp.ultima_reprogramacion
            FROM contratos con
            INNER JOIN cotizaciones cot ON cot.id_cotizacion = con.id_cotizacion
            INNER JOIN clientes     cl  ON cl.id_cliente     = cot.id_cliente
            INNER JOIN personas     p   ON p.id_persona      = cl.id_persona
            LEFT  JOIN empresas     e   ON e.id_empresa      = cl.id_empresa
            LEFT  JOIN (
                SELECT id_contrato,
                       SUM(monto)    AS total_pagado
                FROM pagos
                WHERE estado = 'COMPLETADO'
                GROUP BY id_contrato
            ) pg ON pg.id_contrato = con.id_contrato
            LEFT  JOIN (
                SELECT id_contrato,
                       COUNT(*)          AS total_reprogramaciones,
                       MAX(fecha_cambio) AS ultima_reprogramacion
                FROM reprogramaciones
                GROUP BY id_contrato
            ) rp ON rp.id_contrato = con.id_contrato
        ");

        // ============================================================
        // VISTAS ADICIONALES — negocio y dashboards
        // ============================================================

        // ------------------------------------------------------------
        // v_saldo_contratos
        // Resumen financiero por contrato — útil para dashboard de cobros
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_saldo_contratos");
        $this->db->query("
            CREATE VIEW v_saldo_contratos AS
            SELECT
                con.id_contrato,
                CONCAT(p.nombres, ' ', p.apellidos) AS cliente,
                con.total_final,
                con.adelanto,
                COALESCE(SUM(pg.monto), 0)                                     AS total_pagado,
                con.total_final - con.adelanto - COALESCE(SUM(pg.monto), 0)    AS saldo_pendiente,
                ROUND(
                    (con.adelanto + COALESCE(SUM(pg.monto), 0)) / con.total_final * 100
                , 2)                                                            AS porcentaje_cobrado,
                con.estado
            FROM contratos  con
            INNER JOIN cotizaciones cot ON cot.id_cotizacion = con.id_cotizacion
            INNER JOIN clientes     cl  ON cl.id_cliente     = cot.id_cliente
            INNER JOIN personas     p   ON p.id_persona      = cl.id_persona
            LEFT  JOIN pagos        pg  ON pg.id_contrato    = con.id_contrato
                                      AND pg.estado          = 'COMPLETADO'
            GROUP BY
                con.id_contrato, p.nombres, p.apellidos,
                con.total_final, con.adelanto, con.estado
        ");

        // ------------------------------------------------------------
        // v_ingresos_por_mes
        // Total cobrado por mes — útil para gráficas de ingresos
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_ingresos_por_mes");
        $this->db->query("
            CREATE VIEW v_ingresos_por_mes AS
            SELECT
                YEAR(pg.fecha)                         AS anio,
                MONTH(pg.fecha)                        AS mes,
                DATE_FORMAT(pg.fecha, '%Y-%m')         AS periodo,
                COUNT(DISTINCT pg.id_contrato)         AS contratos_con_pago,
                COUNT(pg.id_pago)                      AS cantidad_pagos,
                SUM(pg.monto)                          AS total_cobrado,
                fp.forma_pago,
                fp.tipo_pago
            FROM pagos       pg
            INNER JOIN formas_pago fp ON fp.id_form_pago = pg.id_form_pago
            WHERE pg.estado = 'COMPLETADO'
            GROUP BY
                YEAR(pg.fecha), MONTH(pg.fecha),
                DATE_FORMAT(pg.fecha, '%Y-%m'),
                fp.forma_pago, fp.tipo_pago
            ORDER BY anio DESC, mes DESC
        ");

        // ------------------------------------------------------------
        // v_cotizaciones_por_estado
        // Conteo de cotizaciones agrupadas por estado y mes
        // útil para gráficas de embudo de ventas (funnel)
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_cotizaciones_por_estado");
        $this->db->query("
            CREATE VIEW v_cotizaciones_por_estado AS
            SELECT
                YEAR(fecha_registro)                    AS anio,
                MONTH(fecha_registro)                   AS mes,
                DATE_FORMAT(fecha_registro, '%Y-%m')    AS periodo,
                estado,
                COUNT(*)                                AS total,
                SUM(total_estimado)                     AS monto_total_estimado
            FROM cotizaciones
            GROUP BY
                YEAR(fecha_registro), MONTH(fecha_registro),
                DATE_FORMAT(fecha_registro, '%Y-%m'), estado
            ORDER BY anio DESC, mes DESC, estado
        ");

        // ------------------------------------------------------------
        // v_paquetes_mas_cotizados
        // Ranking de paquetes por frecuencia de aparición en cotizaciones
        // útil para gráficas de productos más vendidos
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_paquetes_mas_cotizados");
        $this->db->query("
            CREATE VIEW v_paquetes_mas_cotizados AS
            SELECT
                pa.id_paquete,
                pa.nombre_paquete,
                pa.categoria,
                pa.precio_base,
                pa.estado,
                COUNT(cp.id_cot_paquete)    AS veces_cotizado,
                SUM(cp.cantidad)            AS unidades_cotizadas,
                SUM(cp.subtotal)            AS monto_total_cotizado,
                ROUND(AVG(cp.precio_unitario), 2) AS precio_promedio_cotizado
            FROM paquetes    pa
            LEFT JOIN cotizaciones_paquetes cp ON cp.id_paquete = pa.id_paquete
            GROUP BY
                pa.id_paquete, pa.nombre_paquete,
                pa.categoria, pa.precio_base, pa.estado
            ORDER BY veces_cotizado DESC
        ");

        // ------------------------------------------------------------
        // v_clientes_resumen
        // Resumen de actividad por cliente — útil para CRM y fidelización
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_clientes_resumen");
        $this->db->query("
            CREATE VIEW v_clientes_resumen AS
            SELECT
                cl.id_cliente,
                CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo,
                p.telefono,
                p.correo,
                cl.tipo_cliente,
                cl.estado,
                e.razon_social                       AS empresa,
                COUNT(DISTINCT cot.id_cotizacion)    AS total_cotizaciones,
                COUNT(DISTINCT con.id_contrato)      AS total_contratos,
                COALESCE(SUM(con.total_final), 0)    AS monto_total_contratado,
                COALESCE(SUM(pg.monto_pagado), 0)    AS monto_total_pagado,
                MAX(cot.fecha_registro)              AS ultima_cotizacion,
                MAX(con.fecha_contrato)              AS ultimo_contrato
            FROM clientes   cl
            INNER JOIN personas   p   ON p.id_persona   = cl.id_persona
            LEFT  JOIN empresas   e   ON e.id_empresa   = cl.id_empresa
            LEFT  JOIN cotizaciones cot ON cot.id_cliente = cl.id_cliente
            LEFT  JOIN contratos  con ON con.id_cotizacion = cot.id_cotizacion
            LEFT  JOIN (
                SELECT id_contrato, SUM(monto) AS monto_pagado
                FROM pagos WHERE estado = 'COMPLETADO'
                GROUP BY id_contrato
            ) pg ON pg.id_contrato = con.id_contrato
            GROUP BY
                cl.id_cliente, p.nombres, p.apellidos,
                p.telefono, p.correo, cl.tipo_cliente,
                cl.estado, e.razon_social
        ");
        // ------------------------------------------------------------
        // v_agenda_eventos
        // Todos los eventos próximos (cotizaciones y contratos activos)
        // útil para calendario y planificación operativa
        // ------------------------------------------------------------
        $this->db->query("DROP VIEW IF EXISTS v_agenda_eventos");
        $this->db->query("
            CREATE VIEW v_agenda_eventos AS
            SELECT
                'COTIZACION'                                AS origen,
                cot.id_cotizacion                          AS id_referencia,
                cot.nombre_cotizacion                      AS nombre_evento,
                cot.estado,
                cot.fecha_hora_inicio,
                cot.fecha_hora_fin,
                cot.direccion,
                cot.referencia                             AS referencia_lugar,
                CONCAT(p.nombres, ' ', p.apellidos)        AS cliente,
                p.telefono,
                cot.total_estimado                         AS monto
            FROM cotizaciones cot
            INNER JOIN clientes cl ON cl.id_cliente = cot.id_cliente
            INNER JOIN personas p  ON p.id_persona  = cl.id_persona
            WHERE cot.fecha_hora_inicio >= CURRENT_DATE
              AND cot.estado NOT IN ('RECHAZADA', 'EXPIRADA')
 
            UNION ALL
 
            SELECT
                'CONTRATO'                                  AS origen,
                con.id_contrato                            AS id_referencia,
                cot.nombre_cotizacion                      AS nombre_evento,
                con.estado,
                con.fecha_hora_inicio,
                con.fecha_hora_fin,
                cot.direccion,
                cot.referencia                             AS referencia_lugar,
                CONCAT(p.nombres, ' ', p.apellidos)        AS cliente,
                p.telefono,
                con.total_final                            AS monto
            FROM contratos  con
            INNER JOIN cotizaciones cot ON cot.id_cotizacion = con.id_cotizacion
            INNER JOIN clientes     cl  ON cl.id_cliente     = cot.id_cliente
            INNER JOIN personas     p   ON p.id_persona      = cl.id_persona
            WHERE con.fecha_hora_inicio >= CURRENT_DATE
              AND con.estado = 'ACTIVO'
 
            ORDER BY fecha_hora_inicio ASC
        ");
    }

    public function down()
    {
        $views = [
            'v_agenda_eventos',
            'v_clientes_resumen',
            'v_paquetes_mas_cotizados',
            'v_cotizaciones_por_estado',
            'v_ingresos_por_mes',
            'v_saldo_contratos',
            'v_contratos_completo',
            'v_cotizaciones_completo',
            'v_promociones_completo',
            'v_clientes_completo',
            'v_paquetes_completo',
        ];

        foreach ($views as $view) {
            $this->db->query("DROP VIEW IF EXISTS `{$view}`");
        }
    }
}
