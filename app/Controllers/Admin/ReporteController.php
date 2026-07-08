<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReporteController extends BaseController
{
    // ── Paleta ────────────────────────────────────────────────────────────────
    private const COLOR_HEADER_BG   = 'FF1A1A2E'; // azul oscuro
    private const COLOR_HEADER_FG   = 'FFFFFFFF';
    private const COLOR_KPI_BG      = 'FF16213E';
    private const COLOR_KPI_LABEL   = 'FFB8963E'; // dorado
    private const COLOR_ROW_ALT     = 'FFF5F5F5';
    private const COLOR_TOTAL_BG    = 'FFE8F4E8';
    private const COLOR_BORDER      = 'FFD0D0D0';

    public function mensual(): \CodeIgniter\HTTP\Response
    {
        // ── Parámetro mes ─────────────────────────────────────────────────────
        $mesParam = $this->request->getGet('mes') ?? date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $mesParam)) {
            $mesParam = date('Y-m');
        }

        [$anio, $mes] = explode('-', $mesParam);
        $primerDia   = "{$anio}-{$mes}-01";
        $ultimoDia   = date('Y-m-t', strtotime($primerDia));
        $nombreMes   = $this->_nombreMes((int) $mes) . ' ' . $anio;

        $db = \Config\Database::connect();

        // ── Datos ─────────────────────────────────────────────────────────────
        $contratos = $this->_queryContratos($db, $primerDia, $ultimoDia);
        $pagos     = $this->_queryPagos($db, $primerDia, $ultimoDia);
        $kpis      = $this->_queryKpis($db, $primerDia, $ultimoDia, $anio, $mes);

        // ── Libro ─────────────────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle("Reporte mensual {$nombreMes}")
            ->setCreator('Ronceros Fotografía');

        $this->_sheetResumen($spreadsheet->getActiveSheet(), $kpis, $nombreMes);

        $sheetContratos = $spreadsheet->createSheet();
        $this->_sheetContratos($sheetContratos, $contratos, $nombreMes);

        $sheetPagos = $spreadsheet->createSheet();
        $this->_sheetPagos($sheetPagos, $pagos, $nombreMes);

        $spreadsheet->setActiveSheetIndex(0);

        // ── Descarga ──────────────────────────────────────────────────────────
        $filename = "reporte_{$anio}_{$mes}.xlsx";

        ob_end_clean();

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // ── Queries ───────────────────────────────────────────────────────────────

    private function _queryContratos($db, string $desde, string $hasta): array
    {
        return $db->query("
            SELECT
                co.id_contrato,
                co.fecha_creacion,
                co.estado,
                co.total,
                co.adelanto,
                (co.total - co.adelanto) AS saldo,
                pe.nombre   AS promocion,
                pe.grado,
                c.nombre_colegio
            FROM contratos co
            JOIN cotizaciones   cot ON cot.id_cotizacion = co.id_cotizacion
            JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
            JOIN colegios       c   ON c.id_colegio = pe.id_colegio
            WHERE co.fecha_creacion BETWEEN ? AND ?
            ORDER BY co.fecha_creacion, co.id_contrato
        ", [$desde, $hasta])->getResultArray();
    }

    private function _queryPagos($db, string $desde, string $hasta): array
    {
        return $db->query("
            SELECT
                p.id_pago,
                p.fecha,
                p.monto,
                p.moneda,
                p.forma_pago,
                c.nombre_colegio,
                pe.nombre AS promocion,
                co.id_contrato
            FROM pagos p
            JOIN contratos     co  ON co.id_contrato  = p.id_contrato
            JOIN cotizaciones  cot ON cot.id_cotizacion = co.id_cotizacion
            JOIN promociones_escolares pe ON pe.id_cotizacion = cot.id_cotizacion
            JOIN colegios      c   ON c.id_colegio = pe.id_colegio
            WHERE p.fecha BETWEEN ? AND ?
            ORDER BY p.fecha, p.id_pago
        ", [$desde, $hasta])->getResultArray();
    }

    private function _queryKpis($db, string $desde, string $hasta, string $anio, string $mes): array
    {
        $contratosCreados = (int) $db->query(
            "SELECT COUNT(*) AS n FROM contratos WHERE fecha_creacion BETWEEN ? AND ?",
            [$desde, $hasta]
        )->getRow()->n;

        $ingresosRow = $db->query(
            "SELECT COALESCE(SUM(monto),0) AS total FROM pagos WHERE fecha BETWEEN ? AND ?",
            [$desde, $hasta]
        )->getRow();
        $ingresosMes = (float) $ingresosRow->total;

        $contratosActivos = (int) $db->query(
            "SELECT COUNT(*) AS n FROM contratos WHERE UPPER(estado) = 'ACTIVO'"
        )->getRow()->n;

        $sesionesMes = (int) $db->query(
            "SELECT COUNT(*) AS n FROM sesiones_fotograficas
             WHERE MONTH(fecha_hora_sesion) = ? AND YEAR(fecha_hora_sesion) = ?",
            [$mes, $anio]
        )->getRow()->n;

        $ingresosTotales = (float) $db->query(
            "SELECT COALESCE(SUM(monto),0) AS total FROM pagos"
        )->getRow()->total;

        return compact(
            'contratosCreados', 'ingresosMes',
            'contratosActivos', 'sesionesMes', 'ingresosTotales'
        );
    }

    // ── Hojas ─────────────────────────────────────────────────────────────────

    private function _sheetResumen($sheet, array $k, string $nombreMes): void
    {
        $sheet->setTitle('Resumen');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(22);

        // Título
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', "REPORTE MENSUAL — {$nombreMes}");
        $this->_style($sheet, 'A1:B1', [
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        $sheet->mergeCells('A2:B2');
        $sheet->setCellValue('A2', 'Ronceros Fotografía');
        $this->_style($sheet, 'A2:B2', [
            'font'      => ['italic' => true, 'color' => ['argb' => 'FF888888']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // KPI rows
        $kpis = [
            ['Contratos creados en el mes',  $k['contratosCreados'], '#'],
            ['Ingresos recibidos en el mes',  $k['ingresosMes'],      'S/'],
            ['Contratos activos (total)',      $k['contratosActivos'], '#'],
            ['Sesiones fotográficas en el mes',$k['sesionesMes'],     '#'],
            ['Ingresos totales acumulados',    $k['ingresosTotales'], 'S/'],
        ];

        $row = 4;
        foreach ($kpis as [$label, $valor, $tipo]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $valor);
            $fmt = $tipo === 'S/'
                ? '"S/ "#,##0.00'
                : '#,##0';
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($fmt);
            $this->_style($sheet, "A{$row}", [
                'font' => ['color' => ['argb' => self::COLOR_KPI_LABEL], 'bold' => true],
            ]);
            $this->_style($sheet, "B{$row}", [
                'font'      => ['bold' => true, 'size' => 13],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;
        }

        $this->_outerBorder($sheet, "A4:B" . ($row - 1));
    }

    private function _sheetContratos($sheet, array $rows, string $nombreMes): void
    {
        $sheet->setTitle('Contratos');
        $cols = ['A' => 6, 'B' => 38, 'C' => 28, 'D' => 14, 'E' => 12, 'F' => 14, 'G' => 14, 'H' => 14];
        foreach ($cols as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $this->_tableHeader($sheet, 1, "CONTRATOS — {$nombreMes}",
            ['#', 'Institución', 'Promoción', 'Grado', 'Fecha', 'Estado', 'Total (S/)', 'Adelanto (S/)']);

        $dataRow = 3;
        foreach ($rows as $i => $c) {
            $bg = ($i % 2 === 1) ? self::COLOR_ROW_ALT : 'FFFFFFFF';
            $sheet->setCellValue("A{$dataRow}", $c['id_contrato']);
            $sheet->setCellValue("B{$dataRow}", $c['nombre_colegio']);
            $sheet->setCellValue("C{$dataRow}", $c['promocion']);
            $sheet->setCellValue("D{$dataRow}", $c['grado']);
            $sheet->setCellValue("E{$dataRow}", $c['fecha_creacion']);
            $sheet->setCellValue("F{$dataRow}", $c['estado']);
            $sheet->setCellValue("G{$dataRow}", (float) $c['total']);
            $sheet->setCellValue("H{$dataRow}", (float) $c['adelanto']);

            $sheet->getStyle("G{$dataRow}:H{$dataRow}")
                ->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
            $this->_rowStyle($sheet, "A{$dataRow}:H{$dataRow}", $bg);
            $dataRow++;
        }

        if (empty($rows)) {
            $sheet->mergeCells("A3:H3");
            $sheet->setCellValue('A3', 'Sin contratos en este período.');
            $dataRow = 4;
        }

        // Totales
        $totRow = $dataRow;
        $sheet->mergeCells("A{$totRow}:F{$totRow}");
        $sheet->setCellValue("A{$totRow}", 'TOTAL');
        $totalSum   = array_sum(array_column($rows, 'total'));
        $adelantoSum = array_sum(array_column($rows, 'adelanto'));
        $sheet->setCellValue("G{$totRow}", $totalSum);
        $sheet->setCellValue("H{$totRow}", $adelantoSum);
        $sheet->getStyle("G{$totRow}:H{$totRow}")
            ->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
        $this->_style($sheet, "A{$totRow}:H{$totRow}", [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
        ]);

        $this->_outerBorder($sheet, "A2:H{$totRow}");
    }

    private function _sheetPagos($sheet, array $rows, string $nombreMes): void
    {
        $sheet->setTitle('Pagos');
        $cols = ['A' => 6, 'B' => 14, 'C' => 38, 'D' => 28, 'E' => 18, 'F' => 16];
        foreach ($cols as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $this->_tableHeader($sheet, 1, "PAGOS RECIBIDOS — {$nombreMes}",
            ['#', 'Fecha', 'Institución', 'Promoción', 'Forma de pago', 'Monto (S/)']);

        $dataRow = 3;
        foreach ($rows as $i => $p) {
            $bg = ($i % 2 === 1) ? self::COLOR_ROW_ALT : 'FFFFFFFF';
            $sheet->setCellValue("A{$dataRow}", $p['id_pago']);
            $sheet->setCellValue("B{$dataRow}", $p['fecha']);
            $sheet->setCellValue("C{$dataRow}", $p['nombre_colegio']);
            $sheet->setCellValue("D{$dataRow}", $p['promocion']);
            $sheet->setCellValue("E{$dataRow}", $p['forma_pago'] ?? '—');
            $sheet->setCellValue("F{$dataRow}", (float) $p['monto']);
            $sheet->getStyle("F{$dataRow}")
                ->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
            $this->_rowStyle($sheet, "A{$dataRow}:F{$dataRow}", $bg);
            $dataRow++;
        }

        if (empty($rows)) {
            $sheet->mergeCells("A3:F3");
            $sheet->setCellValue('A3', 'Sin pagos registrados en este período.');
            $dataRow = 4;
        }

        // Total
        $totRow = $dataRow;
        $sheet->mergeCells("A{$totRow}:E{$totRow}");
        $sheet->setCellValue("A{$totRow}", 'TOTAL RECIBIDO');
        $sheet->setCellValue("F{$totRow}", array_sum(array_column($rows, 'monto')));
        $sheet->getStyle("F{$totRow}")
            ->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
        $this->_style($sheet, "A{$totRow}:F{$totRow}", [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
        ]);

        $this->_outerBorder($sheet, "A2:F{$totRow}");
    }

    // ── Helpers de estilo ─────────────────────────────────────────────────────

    private function _tableHeader($sheet, int $startRow, string $title, array $headers): void
    {
        $lastCol = chr(ord('A') + count($headers) - 1);

        $sheet->mergeCells("A{$startRow}:{$lastCol}{$startRow}");
        $sheet->setCellValue("A{$startRow}", $title);
        $this->_style($sheet, "A{$startRow}:{$lastCol}{$startRow}", [
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(26);

        $headerRow = $startRow + 1;
        foreach ($headers as $i => $h) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue("{$col}{$headerRow}", $h);
        }
        $this->_style($sheet, "A{$headerRow}:{$lastCol}{$headerRow}", [
            'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2D4A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(18);
    }

    private function _rowStyle($sheet, string $range, string $bgArgb): void
    {
        $this->_style($sheet, $range, [
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]],
            ],
        ]);
    }

    private function _outerBorder($sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getOutline()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->getColor()->setARGB('FF333333');
    }

    private function _style($sheet, string $range, array $style): void
    {
        $def = [];
        if (isset($style['font'])) {
            $def['font'] = $style['font'];
        }
        if (isset($style['fill'])) {
            $def['fill'] = $style['fill'];
        }
        if (isset($style['alignment'])) {
            $def['alignment'] = $style['alignment'];
        }
        if (isset($style['borders'])) {
            $def['borders'] = $style['borders'];
        }
        $sheet->getStyle($range)->applyFromArray($def);
    }

    private function _nombreMes(int $m): string
    {
        return ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][$m];
    }
}
