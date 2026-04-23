<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ViewCotizacionesIndex extends Migration
{
    public function up()
    {
        $this->db->query(
    "CREATE VIEW vista_cotizaciones_resumen AS
            SELECT
                c.id_cotizacion AS id_cotizacion,
                c.nombre_cotizacion AS cotizacion,
                CONCAT(p.nombres, ' ', p.apellidos) AS cliente,
                c.fecha_hora_inicio AS fecha_evento,
                c.fecha_registro AS fecha_creado,
                c.total_estimado AS total,
                c.estado AS estado
            FROM cotizaciones c
            JOIN clientes cl ON cl.id_cliente = c.id_cliente
            JOIN personas p ON p.id_persona = cl.id_persona;
        ");
    }

    public function down()
    {
        $this->db->query("DROP VIEW IF EXISTS vista_cotizaciones_resumen");
    }
}
