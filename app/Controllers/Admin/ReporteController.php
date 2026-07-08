<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\ConnectionInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController extends BaseController
{
    private const MODULOS_VALIDOS = ['kpi', 'cotizaciones', 'contratos', 'pagos', 'sesiones'];

    private const COLOR_TITLE_BG  = 'FF1A1A2E';
    private const COLOR_HEAD_BG   = 'FF2D4A8A';
    private const COLOR_FG        = 'FFFFFFFF';
    private const COLOR_ROW_ALT   = 'FFF5F5F5';
    private const COLOR_TOTAL_BG  = 'FFE8F4E8';

    // ── Página generador ──────────────────────────────────────────────────────

    public function index(): string
    {
        return view('admin/reportes', [
            'header' => view('Layouts/header'),
            'footer' => view('Layouts/footer'),
        ]);
    }

    // ── Preview AJAX (GET) ───────────────────────────────────────────────────

    public function preview()
    {
        [$primerDia, $ultimoDia, $modulos, $desde, $hasta] = $this->_parseGet();
        $db = \Config\Database::connect();

        $out = ['rango' => $this->_rangoLabel($desde, $hasta), 'modulos' => []];

        foreach ($modulos as $m) {
            $out['modulos'][$m] = match ($m) {
                'kpi'          => $this->_dataKpi($db, $primerDia, $ultimoDia),
                'cotizaciones' => $this->_dataCotizaciones($db, $primerDia, $ultimoDia),
                'contratos'    => $this->_dataContratos($db, $primerDia, $ultimoDia),
                'pagos'        => $this->_dataPagos($db, $primerDia, $ultimoDia),
                'sesiones'     => $this->_dataSesiones($db, $primerDia, $ultimoDia),
                default        => [],
            };
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $out]);
    }

    // ── Excel (GET) ───────────────────────────────────────────────────────────

    public function exportar()
    {
        [$primerDia, $ultimoDia, $modulos, $desde, $hasta] = $this->_parseGet();
        $db    = \Config\Database::connect();
        $rango = $this->_rangoLabel($desde, $hasta);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle("Reporte {$rango}")
            ->setCreator('Ronceros Fotografía');

        $first = true;
        foreach ($modulos as $m) {
            $sheet = $first ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $first = false;
            match ($m) {
                'kpi'          => $this->_xlsKpi($sheet, $this->_dataKpi($db, $primerDia, $ultimoDia), $rango),
                'cotizaciones' => $this->_xlsCotizaciones($sheet, $db, $primerDia, $ultimoDia, $rango),
                'contratos'    => $this->_xlsContratos($sheet, $db, $primerDia, $ultimoDia, $rango),
                'pagos'        => $this->_xlsPagos($sheet, $db, $primerDia, $ultimoDia, $rango),
                'sesiones'     => $this->_xlsSesiones($sheet, $db, $primerDia, $ultimoDia, $rango),
                default        => null,
            };
        }

        $spreadsheet->setActiveSheetIndex(0);

        $slug     = preg_replace('/[^a-z0-9]+/', '_', strtolower($rango));
        $filename = "reporte_{$slug}.xlsx";

        ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    // ── PDF con DomPDF (GET) ──────────────────────────────────────────────────

    public function imprimir(): void
    {
        [$primerDia, $ultimoDia, $modulos, $desde, $hasta] = $this->_parseGet();
        $db    = \Config\Database::connect();
        $rango = $this->_rangoLabel($desde, $hasta);

        $data = ['rango' => $rango];
        foreach ($modulos as $m) {
            match ($m) {
                'kpi'          => $data['kpi']         = $this->_dataKpi($db, $primerDia, $ultimoDia),
                'cotizaciones' => $data['cotizaciones'] = $this->_queryCotizaciones($db, $primerDia, $ultimoDia),
                'contratos'    => $data['contratos']    = $this->_queryContratos($db, $primerDia, $ultimoDia),
                'pagos'        => $data['pagos']        = $this->_queryPagos($db, $primerDia, $ultimoDia),
                'sesiones'     => $data['sesiones']     = $this->_querySesiones($db, $primerDia, $ultimoDia),
                default        => null,
            };
        }

        $html = view('admin/reporte_print', $data);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $slug     = preg_replace('/[^a-z0-9]+/', '_', strtolower($rango));
        $filename = "reporte_{$slug}.pdf";

        ob_end_clean();
        $dompdf->stream($filename, ['Attachment' => false]);
        exit;
    }

    // ── Parseo de parámetros ──────────────────────────────────────────────────

    private function _parseGet(): array
    {
        $desde   = $this->request->getGet('desde')   ?? date('Y-m');
        $hasta   = $this->request->getGet('hasta')   ?? date('Y-m');
        $modulos = $this->request->getGet('modulos') ?? self::MODULOS_VALIDOS;
        return $this->_normalizar($desde, $hasta, $modulos);
    }

    private function _normalizar(string $desde, string $hasta, array $modulos): array
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $desde)) $desde = date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $hasta))  $hasta = date('Y-m');
        if ($hasta < $desde) $hasta = $desde;

        $modulos = array_values(array_intersect($modulos, self::MODULOS_VALIDOS));
        if (empty($modulos)) $modulos = self::MODULOS_VALIDOS;

        $primerDia = $desde . '-01';
        $ultimoDia = date('Y-m-t', strtotime($hasta . '-01'));

        return [$primerDia, $ultimoDia, $modulos, $desde, $hasta];
    }

    // ── Queries de datos ──────────────────────────────────────────────────────

    private function _dataKpi(ConnectionInterface $db, string $d, string $h): array
    {
        $ingresos = (float) $db->query(
            "SELECT COALESCE(SUM(monto),0) AS t FROM pagos WHERE fecha BETWEEN ? AND ?", [$d, $h]
        )->getRow()->t;

        $contratos = (int) $db->query(
            "SELECT COUNT(*) AS n FROM contratos WHERE fecha_creacion BETWEEN ? AND ?", [$d, $h]
        )->getRow()->n;

        $activos = (int) $db->query(
            "SELECT COUNT(*) AS n FROM contratos WHERE UPPER(estado)='ACTIVO'"
        )->getRow()->n;

        $sesiones = (int) $db->query(
            "SELECT COUNT(*) AS n FROM sesiones_fotograficas WHERE DATE(fecha_hora_sesion) BETWEEN ? AND ?", [$d, $h]
        )->getRow()->n;

        $ingresosTotal = (float) $db->query(
            "SELECT COALESCE(SUM(monto),0) AS t FROM pagos"
        )->getRow()->t;

        return compact('ingresos', 'contratos', 'activos', 'sesiones', 'ingresosTotal');
    }

    private function _dataCotizaciones(ConnectionInterface $db, string $d, string $h): array
    {
        $total  = (int) $db->query(
            "SELECT COUNT(*) AS n FROM cotizaciones WHERE fecha_registro BETWEEN ? AND ?", [$d, $h]
        )->getRow()->n;

        $estados = $db->query(
            "SELECT estado, COUNT(*) AS total FROM cotizaciones
             WHERE fecha_registro BETWEEN ? AND ? GROUP BY estado", [$d, $h]
        )->getResultArray();

        return compact('total', 'estados');
    }

    private function _dataContratos(ConnectionInterface $db, string $d, string $h): array
    {
        $row = $db->query(
            "SELECT COUNT(*) AS total, COALESCE(SUM(total),0) AS valor
             FROM contratos WHERE fecha_creacion BETWEEN ? AND ?", [$d, $h]
        )->getRow();

        return ['total' => (int) $row->total, 'valor' => (float) $row->valor];
    }

    private function _dataPagos(ConnectionInterface $db, string $d, string $h): array
    {
        $row = $db->query(
            "SELECT COUNT(*) AS total, COALESCE(SUM(monto),0) AS monto
             FROM pagos WHERE fecha BETWEEN ? AND ?", [$d, $h]
        )->getRow();

        return ['total' => (int) $row->total, 'monto' => (float) $row->monto];
    }

    private function _dataSesiones(ConnectionInterface $db, string $d, string $h): array
    {
        $total = (int) $db->query(
            "SELECT COUNT(*) AS n FROM sesiones_fotograficas
             WHERE DATE(fecha_hora_sesion) BETWEEN ? AND ?", [$d, $h]
        )->getRow()->n;

        $estados = $db->query(
            "SELECT estado, COUNT(*) AS total FROM sesiones_fotograficas
             WHERE DATE(fecha_hora_sesion) BETWEEN ? AND ? GROUP BY estado", [$d, $h]
        )->getResultArray();

        return compact('total', 'estados');
    }

    private function _queryCotizaciones(ConnectionInterface $db, string $d, string $h): array
    {
        return $db->query(
            "SELECT cot.id_cotizacion, cot.fecha_registro, cot.estado,
                    (cot.total_estimado - COALESCE(cot.descuento_monto, 0)) AS total,
                    c.nombre_colegio, pe.nombre AS promocion, pe.grado
             FROM cotizaciones cot
             LEFT JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
             LEFT JOIN colegios c ON c.id_colegio = pe.id_colegio
             WHERE cot.fecha_registro BETWEEN ? AND ?
             ORDER BY cot.fecha_registro", [$d, $h]
        )->getResultArray();
    }

    private function _queryContratos(ConnectionInterface $db, string $d, string $h): array
    {
        return $db->query(
            "SELECT co.id_contrato, co.fecha_creacion, co.estado, co.total, co.adelanto,
                    (co.total - co.adelanto) AS saldo,
                    c.nombre_colegio, pe.nombre AS promocion, pe.grado
             FROM contratos co
             JOIN cotizaciones cot ON cot.id_cotizacion = co.id_cotizacion
             JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
             JOIN colegios c ON c.id_colegio = pe.id_colegio
             WHERE co.fecha_creacion BETWEEN ? AND ?
             ORDER BY co.fecha_creacion", [$d, $h]
        )->getResultArray();
    }

    private function _queryPagos(ConnectionInterface $db, string $d, string $h): array
    {
        return $db->query(
            "SELECT p.id_pago, p.fecha, p.monto, p.moneda, p.forma_pago,
                    c.nombre_colegio, pe.nombre AS promocion, co.id_contrato
             FROM pagos p
             JOIN contratos co ON co.id_contrato = p.id_contrato
             JOIN cotizaciones cot ON cot.id_cotizacion = co.id_cotizacion
             JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
             JOIN colegios c ON c.id_colegio = pe.id_colegio
             WHERE p.fecha BETWEEN ? AND ?
             ORDER BY p.fecha", [$d, $h]
        )->getResultArray();
    }

    private function _querySesiones(ConnectionInterface $db, string $d, string $h): array
    {
        return $db->query(
            "SELECT sf.id_sesion, sf.tipo, sf.estado, sf.fecha_hora_sesion,
                    c.nombre_colegio, pe.nombre AS promocion
             FROM sesiones_fotograficas sf
             JOIN promociones_escolares pe ON pe.id_promocion = sf.id_promocion
             JOIN colegios c ON c.id_colegio = pe.id_colegio
             WHERE DATE(sf.fecha_hora_sesion) BETWEEN ? AND ?
             ORDER BY sf.fecha_hora_sesion", [$d, $h]
        )->getResultArray();
    }

    // ── Hojas Excel ───────────────────────────────────────────────────────────

    private function _xlsKpi(Worksheet $sheet, array $k, string $rango): void
    {
        $sheet->setTitle('Resumen KPI');
        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(20);

        $this->_xlsTitle($sheet, 'A1:B1', "RESUMEN KPI — {$rango}");

        $rows = [
            ['Contratos creados en el período', $k['contratos'], '#'],
            ['Ingresos recibidos en el período', $k['ingresos'],  'S/'],
            ['Contratos activos (total)',         $k['activos'],   '#'],
            ['Sesiones en el período',            $k['sesiones'],  '#'],
            ['Ingresos totales acumulados',        $k['ingresosTotal'], 'S/'],
        ];
        $r = 3;
        foreach ($rows as [$label, $val, $tipo]) {
            $sheet->setCellValue("A{$r}", $label);
            $sheet->setCellValue("B{$r}", $val);
            if ($tipo === 'S/') {
                $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
            }
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet->getStyle("B{$r}")->getFont()->setBold(true);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $r++;
        }
    }

    private function _xlsCotizaciones(Worksheet $sheet, ConnectionInterface $db, string $d, string $h, string $rango): void
    {
        $rows = $this->_queryCotizaciones($db, $d, $h);
        $sheet->setTitle('Cotizaciones');
        $this->_xlsTitle($sheet, 'A1:G1', "COTIZACIONES — {$rango}");
        $this->_xlsHeaders($sheet, 2, ['#', 'Fecha', 'Institución', 'Promoción', 'Grado', 'Estado', 'Total (S/)']);
        $colWidths = [6, 14, 36, 30, 14, 14, 14];
        $this->_xlsColWidths($sheet, $colWidths);

        $r = 3;
        foreach ($rows as $i => $row) {
            $bg = ($i % 2 === 1) ? self::COLOR_ROW_ALT : 'FFFFFFFF';
            $sheet->setCellValue("A{$r}", $row['id_cotizacion']);
            $sheet->setCellValue("B{$r}", $row['fecha_registro']);
            $sheet->setCellValue("C{$r}", $row['nombre_colegio'] ?? '—');
            $sheet->setCellValue("D{$r}", $row['promocion'] ?? '—');
            $sheet->setCellValue("E{$r}", $row['grado'] ?? '—');
            $sheet->setCellValue("F{$r}", $row['estado']);
            $sheet->setCellValue("G{$r}", (float) $row['total']);
            $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
            $this->_xlsRowBg($sheet, "A{$r}:G{$r}", $bg);
            $r++;
        }
        $this->_xlsTotal($sheet, "A{$r}:F{$r}", "G{$r}", array_sum(array_column($rows, 'total')), '"S/ "#,##0.00');
    }

    private function _xlsContratos(Worksheet $sheet, ConnectionInterface $db, string $d, string $h, string $rango): void
    {
        $rows = $this->_queryContratos($db, $d, $h);
        $sheet->setTitle('Contratos');
        $this->_xlsTitle($sheet, 'A1:H1', "CONTRATOS — {$rango}");
        $this->_xlsHeaders($sheet, 2, ['#', 'Fecha', 'Institución', 'Promoción', 'Grado', 'Estado', 'Total (S/)', 'Adelanto (S/)']);
        $this->_xlsColWidths($sheet, [6, 13, 34, 28, 13, 12, 14, 14]);

        $r = 3;
        foreach ($rows as $i => $row) {
            $bg = ($i % 2 === 1) ? self::COLOR_ROW_ALT : 'FFFFFFFF';
            $sheet->setCellValue("A{$r}", $row['id_contrato']);
            $sheet->setCellValue("B{$r}", $row['fecha_creacion']);
            $sheet->setCellValue("C{$r}", $row['nombre_colegio']);
            $sheet->setCellValue("D{$r}", $row['promocion']);
            $sheet->setCellValue("E{$r}", $row['grado']);
            $sheet->setCellValue("F{$r}", $row['estado']);
            $sheet->setCellValue("G{$r}", (float) $row['total']);
            $sheet->setCellValue("H{$r}", (float) $row['adelanto']);
            $sheet->getStyle("G{$r}:H{$r}")->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
            $this->_xlsRowBg($sheet, "A{$r}:H{$r}", $bg);
            $r++;
        }
        $sheet->mergeCells("A{$r}:G{$r}");
        $this->_xlsTotal($sheet, "A{$r}:G{$r}", "H{$r}", array_sum(array_column($rows, 'adelanto')), '"S/ "#,##0.00');
    }

    private function _xlsPagos(Worksheet $sheet, ConnectionInterface $db, string $d, string $h, string $rango): void
    {
        $rows = $this->_queryPagos($db, $d, $h);
        $sheet->setTitle('Pagos');
        $this->_xlsTitle($sheet, 'A1:F1', "PAGOS RECIBIDOS — {$rango}");
        $this->_xlsHeaders($sheet, 2, ['#', 'Fecha', 'Institución', 'Forma de pago', 'Moneda', 'Monto']);
        $this->_xlsColWidths($sheet, [6, 13, 36, 18, 10, 14]);

        $r = 3;
        foreach ($rows as $i => $row) {
            $bg = ($i % 2 === 1) ? self::COLOR_ROW_ALT : 'FFFFFFFF';
            $sheet->setCellValue("A{$r}", $row['id_pago']);
            $sheet->setCellValue("B{$r}", $row['fecha']);
            $sheet->setCellValue("C{$r}", $row['nombre_colegio']);
            $sheet->setCellValue("D{$r}", $row['forma_pago'] ?? '—');
            $sheet->setCellValue("E{$r}", $row['moneda']);
            $sheet->setCellValue("F{$r}", (float) $row['monto']);
            $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
            $this->_xlsRowBg($sheet, "A{$r}:F{$r}", $bg);
            $r++;
        }
        $this->_xlsTotal($sheet, "A{$r}:E{$r}", "F{$r}", array_sum(array_column($rows, 'monto')), '"S/ "#,##0.00');
    }

    private function _xlsSesiones(Worksheet $sheet, ConnectionInterface $db, string $d, string $h, string $rango): void
    {
        $rows = $this->_querySesiones($db, $d, $h);
        $sheet->setTitle('Sesiones');
        $this->_xlsTitle($sheet, 'A1:E1', "SESIONES FOTOGRÁFICAS — {$rango}");
        $this->_xlsHeaders($sheet, 2, ['#', 'Fecha y hora', 'Institución', 'Tipo', 'Estado']);
        $this->_xlsColWidths($sheet, [6, 20, 36, 14, 14]);

        $r = 3;
        foreach ($rows as $i => $row) {
            $bg = ($i % 2 === 1) ? self::COLOR_ROW_ALT : 'FFFFFFFF';
            $sheet->setCellValue("A{$r}", $row['id_sesion']);
            $sheet->setCellValue("B{$r}", $row['fecha_hora_sesion']);
            $sheet->setCellValue("C{$r}", $row['nombre_colegio']);
            $sheet->setCellValue("D{$r}", ucfirst($row['tipo']));
            $sheet->setCellValue("E{$r}", ucfirst($row['estado']));
            $this->_xlsRowBg($sheet, "A{$r}:E{$r}", $bg);
            $r++;
        }
        if (empty($rows)) {
            $sheet->mergeCells("A3:E3");
            $sheet->setCellValue('A3', 'Sin sesiones en este período.');
        }
    }

    // ── Helpers Excel ─────────────────────────────────────────────────────────

    private function _xlsTitle(Worksheet $sheet, string $range, string $text): void
    {
        $sheet->mergeCells($range);
        $sheet->setCellValue(explode(':', $range)[0], $text);
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => self::COLOR_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TITLE_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);
    }

    private function _xlsHeaders(Worksheet $sheet, int $row, array $headers): void
    {
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . $row, $h);
        }
        $last = chr(65 + count($headers) - 1);
        $sheet->getStyle("A{$row}:{$last}{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEAD_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(18);
    }

    private function _xlsColWidths(Worksheet $sheet, array $widths): void
    {
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension(chr(65 + $i))->setWidth($w);
        }
    }

    private function _xlsRowBg(Worksheet $sheet, string $range, string $argb): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $argb]],
        ]);
    }

    private function _xlsTotal(Worksheet $sheet, string $labelRange, string $valueCell, float $value, string $fmt): void
    {
        $sheet->mergeCells($labelRange);
        $sheet->setCellValue(explode(':', $labelRange)[0], 'TOTAL');
        $sheet->setCellValue($valueCell, $value);
        $sheet->getStyle($valueCell)->getNumberFormat()->setFormatCode($fmt);
        $end = explode(':', $labelRange)[1];
        $sheet->getStyle(explode(':', $labelRange)[0] . ':' . $end)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
        ]);
        $sheet->getStyle($valueCell)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
        ]);
    }

    // ── Helpers generales ─────────────────────────────────────────────────────

    private function _rangoLabel(string $desde, string $hasta): string
    {
        $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                  'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        [$ay, $am] = explode('-', $desde);
        [$by, $bm] = explode('-', $hasta);

        $a = $meses[(int)$am] . ' ' . $ay;
        $b = $meses[(int)$bm] . ' ' . $by;
        return $a === $b ? $a : "{$a} – {$b}";
    }
}
