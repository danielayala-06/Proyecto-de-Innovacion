<?php

/**
 * @file    Home.php
 * @package App\Controllers
 *
 * Controlador del Dashboard principal de la aplicación.
 * Calcula las métricas de resumen (contratos activos, total de clientes
 * e ingresos del mes) usando consultas directas a la base de datos.
 */

namespace App\Controllers;

/**
 * Sirve el dashboard con métricas de negocio en tiempo real.
 *
 * Ruta:
 *  - GET / → index()
 *
 * Las métricas se calculan directamente en este controlador (sin Service)
 * porque son consultas simples de agregación que no requieren lógica de negocio.
 */
class Home extends BaseController
{
    /**
     * Renderiza el dashboard principal con las métricas de resumen.
     *
     * Métricas calculadas:
     *  - `contratosActivos` : COUNT de contratos con estado = 'ACTIVO'.
     *  - `totalClientes`    : COUNT de clientes con estado = 'ACTIVO'.
     *  - `ingresos`         : SUM de pagos.monto del mes y año actuales (CURDATE()).
     *
     * @return string HTML de la vista renderizada.
     */
    public function index(): string
    {
        $db = \Config\Database::connect();

        // Contratos en estado ACTIVO
        $contratosActivos = (int) $db->table('contratos')
            ->where('UPPER(estado)', 'ACTIVO')
            ->countAllResults();

        // Clientes activos en el sistema
        $totalClientes = (int) $db->table('clientes')
            ->where('UPPER(estado)', 'ACTIVO')
            ->countAllResults();

        // Ingresos del mes en curso (suma de todos los pagos registrados en el mes/año actual)
        $row = $db->table('pagos')
            ->selectSum('monto')
            ->where("MONTH(fecha) = MONTH(CURDATE())", null, false)
            ->where("YEAR(fecha)  = YEAR(CURDATE())",  null, false)
            ->get()->getRowArray();
        $ingresos = (float) ($row['monto'] ?? 0);

        $data = [
            'header'           => view('Layouts/header'),
            'footer'           => view('Layouts/footer'),
            'contratosActivos' => $contratosActivos,
            'totalClientes'    => $totalClientes,
            'ingresos'         => $ingresos,
        ];

        return view('index', $data);
    }
}
