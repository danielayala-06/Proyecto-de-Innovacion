<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();

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
            ->get()->getRowArray();
        $ingresos = (float)($row['monto'] ?? 0);


        $data = [
            'header'            => view('Layouts/header'),
            'footer'            => view('Layouts/footer'),
            'contratosActivos'  => $contratosActivos,
            'totalClientes'     => $totalClientes,
            'ingresos'          => $ingresos,
        ];

        return view('index', $data);
    }
}
