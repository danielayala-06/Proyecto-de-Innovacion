<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ViewCotizacionesFull extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE VIEW vw_cotizaciones_full AS
            SELECT 
                c.id_cotizacion,
                c.fecha_registro,
                c.estado,
                c.total_estimado,

                -- Cliente
                cl.id_cliente,
                p.nombres,
                p.apellidos,
                p.numero_documento,
                p.telefono,

                -- Agregados de paquetes
                COUNT(cp.id_paquete) AS cantidad_paquetes,
                COALESCE(SUM(pa.precio_base), 0) AS total_paquetes,

                -- Lista de paquetes
                GROUP_CONCAT(pa.nombre_paquete SEPARATOR ', ') AS paquetes

            FROM Cotizaciones c

            JOIN Clientes cl 
                ON c.id_cliente = cl.id_cliente

            JOIN Personas p 
                ON cl.id_persona = p.id_persona

            LEFT JOIN Cotizaciones_paquetes cp 
                ON c.id_cotizacion = cp.id_cotizacion

            LEFT JOIN Paquetes pa 
                ON cp.id_paquete = pa.id_paquete

            GROUP BY 
                c.id_cotizacion,
                c.fecha_registro,
                c.estado,
                c.total_estimado,
                cl.id_cliente,
                p.nombres,
                p.apellidos,
                p.numero_documento,
                p.telefono
        ");
    }

    public function down()
    {
        $this->db->query("DROP VIEW IF EXISTS vw_cotizaciones_full");
    }
}
