<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ViewCotizacionesPersona extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE VIEW vw_cotizaciones_detalle AS
            SELECT 
                c.id_cotizacion,
                c.fecha_registro,
                c.total_estimado,
                cl.id_cliente,
                p.nombres,
                p.apellidos,
                p.numero_documento,
                p.telefono
            FROM Cotizaciones c
            JOIN ApiClientes cl ON c.id_cliente = cl.id_cliente
            JOIN Personas p ON cl.id_persona = p.id_persona
        ");
    }

    public function down()
    {
        $this->db->query("DROP VIEW IF EXISTS vw_cotizaciones_detalle");
    }
}
