<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();

        $cotizaciones = (int) $db->table('cotizaciones')->countAllResults();

        $contratosActivos = (int) $db->table('contratos')
            ->where('UPPER(estado)', 'ACTIVO')
            ->countAllResults();

        $promocioneActivas = (int) $db->table('prom_promociones')
            ->where('activa', 1)
            ->countAllResults();

        $sesionesEstesMes = (int) $db->table('sesiones_fotograficas')
            ->where("MONTH(fecha_hora_sesion) = MONTH(CURDATE())", null, false)
            ->where("YEAR(fecha_hora_sesion)  = YEAR(CURDATE())",  null, false)
            ->countAllResults();

        $row = $db->table('pagos')
            ->selectSum('monto')
            ->get()->getRowArray();
        $ingresos = (float) ($row['monto'] ?? 0);

        $proximasSesiones = $db->table('sesiones_fotograficas sf')
            ->select('c.nombre_colegio AS cliente, sf.tipo, sf.fecha_hora_sesion AS fecha')
            ->join('prom_promociones pp', 'pp.id = sf.id_promocion')
            ->join('colegios c', 'c.id_colegio = pp.colegio_id')
            ->where('sf.estado', 'pendiente')
            ->where('sf.fecha_hora_sesion >=', date('Y-m-d H:i:s'))
            ->orderBy('sf.fecha_hora_sesion', 'ASC')
            ->limit(8)
            ->get()->getResultArray();

        return view('index', [
            'header'            => view('Layouts/header'),
            'footer'            => view('Layouts/footer'),
            'cotizaciones'      => $cotizaciones,
            'contratosActivos'  => $contratosActivos,
            'promocioneActivas' => $promocioneActivas,
            'sesionesEstesMes'  => $sesionesEstesMes,
            'ingresos'          => $ingresos,
            'proximasSesiones'  => $proximasSesiones,
        ]);
    }
}
