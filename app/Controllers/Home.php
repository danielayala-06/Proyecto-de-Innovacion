<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();

        $sesionesEstesMes = (int) $db->table('cotizaciones')
            ->where("MONTH(fecha_hora_inicio) = MONTH(CURDATE())", null, false)
            ->where("YEAR(fecha_hora_inicio)  = YEAR(CURDATE())",  null, false)
            ->where('UPPER(estado)', 'APROBADA')
            ->countAllResults();

        $contratosActivos = (int) $db->table('contratos')
            ->where('UPPER(estado)', 'ACTIVO')
            ->countAllResults();

        $totalClientes = (int) $db->table('clientes')
            ->where('UPPER(estado)', 'ACTIVO')
            ->countAllResults();

        $row = $db->table('pagos')
            ->selectSum('monto')
            ->where("MONTH(fecha) = MONTH(CURDATE())", null, false)
            ->where("YEAR(fecha)  = YEAR(CURDATE())",  null, false)
            ->where('UPPER(estado)', 'COMPLETADO')
            ->get()->getRowArray();
        $ingresos = (float)($row['monto'] ?? 0);

        $proximasSesiones = $db->table('cotizaciones cot')
            ->select('CONCAT(p.nombres, " ", p.apellidos) AS cliente,
                      cot.nombre_cotizacion AS tipo,
                      cot.fecha_hora_inicio AS fecha')
            ->join('clientes cl', 'cl.id_cliente = cot.id_cliente')
            ->join('personas p',  'p.id_persona = cl.id_persona')
            ->where('cot.fecha_hora_inicio >= NOW()', null, false)
            ->where('UPPER(cot.estado)', 'APROBADA')
            ->orderBy('cot.fecha_hora_inicio', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        $data = [
            'header'            => view('Layouts/header'),
            'footer'            => view('Layouts/footer'),
            'sesionesEstesMes'  => $sesionesEstesMes,
            'contratosActivos'  => $contratosActivos,
            'totalClientes'     => $totalClientes,
            'ingresos'          => $ingresos,
            'proximasSesiones'  => $proximasSesiones,
        ];

        return view('index', $data);
    }
}
