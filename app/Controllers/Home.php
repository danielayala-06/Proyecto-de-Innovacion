<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();

        // ── KPIs ─────────────────────────────────────────────────────────────
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

        $row = $db->table('pagos')->selectSum('monto')->get()->getRowArray();
        $ingresos = (float) ($row['monto'] ?? 0);

        // ── Próximas sesiones ─────────────────────────────────────────────────
        $proximasSesiones = $db->table('sesiones_fotograficas sf')
            ->select('c.nombre_colegio AS cliente, sf.tipo, sf.fecha_hora_sesion AS fecha')
            ->join('promociones_escolares pe', 'pe.id_promocion = sf.id_promocion')
            ->join('colegios c', 'c.id_colegio = pe.id_colegio')
            ->where('sf.estado', 'pendiente')
            ->where('sf.fecha_hora_sesion >=', date('Y-m-d H:i:s'))
            ->orderBy('sf.fecha_hora_sesion', 'ASC')
            ->limit(8)
            ->get()->getResultArray();

        // ── Datos para gráficas ───────────────────────────────────────────────
        $chartData = $this->_buildChartData($db);

        return view('index', [
            'header'            => view('Layouts/header'),
            'footer'            => view('Layouts/footer'),
            'cotizaciones'      => $cotizaciones,
            'contratosActivos'  => $contratosActivos,
            'promocioneActivas' => $promocioneActivas,
            'sesionesEstesMes'  => $sesionesEstesMes,
            'ingresos'          => $ingresos,
            'proximasSesiones'  => $proximasSesiones,
            'chartData'         => json_encode($chartData),
        ]);
    }

    private function _buildChartData(\CodeIgniter\Database\ConnectionInterface $db): array
    {
        // Últimos 12 meses como scaffold (YYYY-MM → índice)
        $scaffold = [];
        $labels   = [];
        $meses    = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        for ($i = 11; $i >= 0; $i--) {
            $key           = date('Y-m', strtotime("-{$i} months"));
            $scaffold[$key] = 0;
            $labels[]      = $meses[(int) date('n', strtotime("-{$i} months")) - 1];
        }

        // Cotizaciones por mes
        $rows = $db->query(
            "SELECT DATE_FORMAT(fecha_registro,'%Y-%m') AS mes, COUNT(*) AS total
             FROM cotizaciones
             WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY mes"
        )->getResultArray();
        $cotizMes = $scaffold;
        foreach ($rows as $r) {
            if (isset($cotizMes[$r['mes']])) {
                $cotizMes[$r['mes']] = (int) $r['total'];
            }
        }

        // Estado cotizaciones
        $estadoCotizRows = $db->query(
            "SELECT estado, COUNT(*) AS total FROM cotizaciones GROUP BY estado"
        )->getResultArray();
        $estadoCotizLabels = array_column($estadoCotizRows, 'estado');
        $estadoCotizValues = array_map('intval', array_column($estadoCotizRows, 'total'));

        // Contratos por mes
        $rows = $db->query(
            "SELECT DATE_FORMAT(fecha_creacion,'%Y-%m') AS mes, COUNT(*) AS total
             FROM contratos
             WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY mes"
        )->getResultArray();
        $contratosMes = $scaffold;
        foreach ($rows as $r) {
            if (isset($contratosMes[$r['mes']])) {
                $contratosMes[$r['mes']] = (int) $r['total'];
            }
        }

        // Estado contratos
        $estadoConRows = $db->query(
            "SELECT estado, COUNT(*) AS total FROM contratos GROUP BY estado"
        )->getResultArray();
        $estadoConLabels = array_column($estadoConRows, 'estado');
        $estadoConValues = array_map('intval', array_column($estadoConRows, 'total'));

        // Sesiones por mes
        $rows = $db->query(
            "SELECT DATE_FORMAT(fecha_hora_sesion,'%Y-%m') AS mes, COUNT(*) AS total
             FROM sesiones_fotograficas
             WHERE fecha_hora_sesion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY mes"
        )->getResultArray();
        $sesionesMes = $scaffold;
        foreach ($rows as $r) {
            if (isset($sesionesMes[$r['mes']])) {
                $sesionesMes[$r['mes']] = (int) $r['total'];
            }
        }

        // Estado sesiones
        $estadoSesRows = $db->query(
            "SELECT estado, COUNT(*) AS total FROM sesiones_fotograficas GROUP BY estado"
        )->getResultArray();
        $estadoSesLabels = array_column($estadoSesRows, 'estado');
        $estadoSesValues = array_map('intval', array_column($estadoSesRows, 'total'));

        // Productos más vendidos (top 7 por cantidad en detalles de cotizaciones)
        $prodRows = $db->query(
            "SELECT p.nombre_producto, SUM(cd.cantidad) AS total_vendido
             FROM cotizaciones_detalles cd
             JOIN paquetes pk  ON pk.id_paquete  = cd.id_referencia AND cd.tipo_item = 'paquete'
             JOIN paquetes_productos pprod ON pprod.id_paquete = pk.id_paquete
             JOIN productos p ON p.id_producto = pprod.id_producto
             GROUP BY p.id_producto, p.nombre_producto
             ORDER BY total_vendido DESC
             LIMIT 7"
        )->getResultArray();
        $prodLabels = array_column($prodRows, 'nombre_producto');
        $prodValues = array_map('intval', array_column($prodRows, 'total_vendido'));

        // Valor contratado por institución
        $instRows = $db->query(
            "SELECT c.nombre_colegio, SUM(co.total) AS valor_total
             FROM contratos co
             JOIN cotizaciones cot ON cot.id_cotizacion = co.id_cotizacion
             JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
             JOIN colegios c ON c.id_colegio = pe.id_colegio
             GROUP BY c.id_colegio, c.nombre_colegio
             ORDER BY valor_total DESC
             LIMIT 8"
        )->getResultArray();
        $instLabels = array_column($instRows, 'nombre_colegio');
        $instValues = array_map('floatval', array_column($instRows, 'valor_total'));

        return [
            'meses'              => $labels,
            'cotizacionesPorMes' => array_values($cotizMes),
            'estadoCotizaciones' => ['labels' => $estadoCotizLabels, 'values' => $estadoCotizValues],
            'contratosPorMes'    => array_values($contratosMes),
            'estadoContratos'    => ['labels' => $estadoConLabels,   'values' => $estadoConValues],
            'sesionesPorMes'     => array_values($sesionesMes),
            'estadoSesiones'     => ['labels' => $estadoSesLabels,   'values' => $estadoSesValues],
            'productosMasVendidos' => ['labels' => $prodLabels, 'values' => $prodValues],
            'valorPorInstitucion'  => ['labels' => $instLabels, 'values' => $instValues],
        ];
    }
}
