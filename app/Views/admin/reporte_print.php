<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reporte — <?= esc($rango) ?></title>
<style>
/* ── Reset & base ─────────────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 13px; }
body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a2e; background: #fff; }

/* ── Page layout ──────────────────────────────────────────────────────────── */
.page { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }

/* ── Header ──────────────────────────────────────────────────────────────── */
.rpt-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #1a1a2e; padding-bottom: .8rem; margin-bottom: 1.5rem; }
.rpt-header .brand { font-size: 1.1rem; font-weight: 800; letter-spacing: -.01em; color: #1a1a2e; }
.rpt-header .brand span { color: #2d4a8a; }
.rpt-header .meta { text-align: right; font-size: .78rem; color: #555; }
.rpt-header .meta strong { display: block; font-size: .85rem; color: #1a1a2e; }

/* ── Section titles ──────────────────────────────────────────────────────── */
.sec-title {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #fff;
    background: #2d4a8a;
    padding: .35rem .75rem;
    margin: 1.4rem 0 .6rem;
    border-radius: 3px;
}

/* ── KPI grid ────────────────────────────────────────────────────────────── */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .6rem; margin-bottom: 1rem; }
.kpi-box { border: 1px solid #d0d0d0; border-radius: 4px; padding: .55rem .7rem; background: #f8f9fb; }
.kpi-box .kpi-label { font-size: .65rem; color: #666; text-transform: uppercase; letter-spacing: .04em; }
.kpi-box .kpi-val   { font-size: 1.3rem; font-weight: 700; color: #1a1a2e; margin-top: .1rem; }

/* ── Tables ──────────────────────────────────────────────────────────────── */
table { width: 100%; border-collapse: collapse; font-size: .78rem; }
thead { background: #2d4a8a; color: #fff; }
thead th { padding: .38rem .5rem; text-align: left; font-weight: 600; font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
tbody tr:nth-child(even) { background: #f5f5f5; }
tbody td { padding: .32rem .5rem; border-bottom: 1px solid #e8e8e8; color: #222; }
tfoot td { font-weight: 700; background: #e8f4e8; padding: .38rem .5rem; border-top: 2px solid #2d4a8a; }

.num { text-align: right; font-variant-numeric: tabular-nums; }
.badge { display: inline-block; font-size: .65rem; padding: .1rem .45rem; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
.badge-verde   { background: #d4edda; color: #155724; }
.badge-rojo    { background: #f8d7da; color: #721c24; }
.badge-gris    { background: #e2e3e5; color: #383d41; }
.badge-amarillo{ background: #fff3cd; color: #856404; }

/* ── Print button (hidden on print) ─────────────────────────────────────── */
.print-bar {
    position: sticky;
    top: 0;
    background: #1a1a2e;
    color: #fff;
    padding: .55rem 1.5rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    font-size: .8rem;
    z-index: 100;
}
.print-bar button {
    background: #2d4a8a;
    color: #fff;
    border: none;
    padding: .35rem .9rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: .8rem;
    font-weight: 600;
}
.print-bar button:hover { filter: brightness(1.2); }

/* ── Footer ──────────────────────────────────────────────────────────────── */
.rpt-footer { margin-top: 2rem; border-top: 1px solid #d0d0d0; padding-top: .6rem; font-size: .68rem; color: #999; display: flex; justify-content: space-between; }

/* ── Print media ─────────────────────────────────────────────────────────── */
@media print {
    .print-bar { display: none !important; }
    body { font-size: 11px; }
    .kpi-grid { grid-template-columns: repeat(4, 1fr); }
    .page { padding: 0; }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; }
    .sec-title { margin-top: 1rem; }
}
</style>
</head>
<body>

<!-- Barra de impresión -->
<div class="print-bar">
    <span>Vista previa de impresión — <?= esc($rango) ?></span>
    <button onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
    <button onclick="window.close()" style="background:#555">✕ Cerrar</button>
</div>

<div class="page">

    <!-- Encabezado -->
    <div class="rpt-header">
        <div class="brand">Ronceros <span>Fotografía</span></div>
        <div class="meta">
            <strong>Reporte de actividad</strong>
            <?= esc($rango) ?><br>
            Generado el <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <!-- ── KPI ──────────────────────────────────────────────────────────────── -->
    <?php if (isset($kpi)): ?>
    <div class="sec-title">Resumen KPI</div>
    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="kpi-label">Contratos creados</div>
            <div class="kpi-val"><?= $kpi['contratos'] ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Contratos activos</div>
            <div class="kpi-val"><?= $kpi['activos'] ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Ingresos período</div>
            <div class="kpi-val">S/ <?= number_format($kpi['ingresos'], 0, '.', ',') ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Sesiones período</div>
            <div class="kpi-val"><?= $kpi['sesiones'] ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Cotizaciones ─────────────────────────────────────────────────────── -->
    <?php if (isset($cotizaciones)): ?>
    <div class="sec-title">Cotizaciones</div>
    <?php if (empty($cotizaciones)): ?>
        <p style="color:#999;font-size:.8rem;margin:.5rem 0 1rem">Sin registros en el período.</p>
    <?php else: ?>
    <?php
        $totalCotiz = array_sum(array_column($cotizaciones, 'total'));
    ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Institución</th>
                <th>Promoción</th>
                <th>Grado</th>
                <th>Estado</th>
                <th class="num">Total (S/)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cotizaciones as $r): ?>
            <tr>
                <td><?= $r['id_cotizacion'] ?></td>
                <td><?= date('d/m/Y', strtotime($r['fecha_registro'])) ?></td>
                <td><?= esc($r['nombre_colegio'] ?? '—') ?></td>
                <td><?= esc($r['promocion'] ?? '—') ?></td>
                <td><?= esc($r['grado'] ?? '—') ?></td>
                <td><?= _badgeEstado($r['estado']) ?></td>
                <td class="num"><?= number_format((float)$r['total'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total (<?= count($cotizaciones) ?> registros)</td>
                <td class="num">S/ <?= number_format($totalCotiz, 2) ?></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ── Contratos ────────────────────────────────────────────────────────── -->
    <?php if (isset($contratos)): ?>
    <div class="sec-title">Contratos</div>
    <?php if (empty($contratos)): ?>
        <p style="color:#999;font-size:.8rem;margin:.5rem 0 1rem">Sin registros en el período.</p>
    <?php else: ?>
    <?php
        $totalContratos = array_sum(array_column($contratos, 'total'));
        $totalPagado    = array_sum(array_column($contratos, 'adelanto'));
        $totalSaldo     = array_sum(array_column($contratos, 'saldo'));
    ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Institución</th>
                <th>Promoción</th>
                <th>Grado</th>
                <th>Estado</th>
                <th class="num">Total (S/)</th>
                <th class="num">Adelanto (S/)</th>
                <th class="num">Saldo (S/)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($contratos as $r): ?>
            <tr>
                <td><?= $r['id_contrato'] ?></td>
                <td><?= date('d/m/Y', strtotime($r['fecha_creacion'])) ?></td>
                <td><?= esc($r['nombre_colegio'] ?? '—') ?></td>
                <td><?= esc($r['promocion'] ?? '—') ?></td>
                <td><?= esc($r['grado'] ?? '—') ?></td>
                <td><?= _badgeEstado($r['estado']) ?></td>
                <td class="num"><?= number_format((float)$r['total'], 2) ?></td>
                <td class="num"><?= number_format((float)$r['adelanto'], 2) ?></td>
                <td class="num"><?= number_format((float)$r['saldo'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total (<?= count($contratos) ?> registros)</td>
                <td class="num">S/ <?= number_format($totalContratos, 2) ?></td>
                <td class="num">S/ <?= number_format($totalPagado, 2) ?></td>
                <td class="num">S/ <?= number_format($totalSaldo, 2) ?></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ── Pagos ─────────────────────────────────────────────────────────────── -->
    <?php if (isset($pagos)): ?>
    <div class="sec-title">Pagos</div>
    <?php if (empty($pagos)): ?>
        <p style="color:#999;font-size:.8rem;margin:.5rem 0 1rem">Sin registros en el período.</p>
    <?php else: ?>
    <?php $totalPagos = array_sum(array_column($pagos, 'monto')); ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Institución</th>
                <th>Promoción</th>
                <th>Contrato</th>
                <th>Forma de pago</th>
                <th>Moneda</th>
                <th class="num">Monto</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pagos as $r): ?>
            <tr>
                <td><?= $r['id_pago'] ?></td>
                <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                <td><?= esc($r['nombre_colegio'] ?? '—') ?></td>
                <td><?= esc($r['promocion'] ?? '—') ?></td>
                <td><?= $r['id_contrato'] ?></td>
                <td><?= esc(ucfirst($r['forma_pago'] ?? '—')) ?></td>
                <td><?= esc($r['moneda'] ?? 'PEN') ?></td>
                <td class="num"><?= number_format((float)$r['monto'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">Total (<?= count($pagos) ?> transacciones)</td>
                <td class="num">S/ <?= number_format($totalPagos, 2) ?></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ── Sesiones ──────────────────────────────────────────────────────────── -->
    <?php if (isset($sesiones)): ?>
    <div class="sec-title">Sesiones fotográficas</div>
    <?php if (empty($sesiones)): ?>
        <p style="color:#999;font-size:.8rem;margin:.5rem 0 1rem">Sin registros en el período.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha y hora</th>
                <th>Institución</th>
                <th>Promoción</th>
                <th>Tipo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($sesiones as $r): ?>
            <tr>
                <td><?= $r['id_sesion'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($r['fecha_hora_sesion'])) ?></td>
                <td><?= esc($r['nombre_colegio'] ?? '—') ?></td>
                <td><?= esc($r['promocion'] ?? '—') ?></td>
                <td><?= esc(ucfirst($r['tipo'] ?? '—')) ?></td>
                <td><?= _badgeEstado($r['estado']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total: <?= count($sesiones) ?> sesiones</td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Pie de página -->
    <div class="rpt-footer">
        <span>Ronceros Fotografía · Sistema de gestión</span>
        <span>Período: <?= esc($rango) ?></span>
    </div>

</div>

<?php
function _badgeEstado(?string $estado): string
{
    if (!$estado) return '<span class="badge badge-gris">—</span>';
    $e = strtolower($estado);
    $cls = match (true) {
        in_array($e, ['activo', 'completado', 'aprobada', 'confirmada'])  => 'badge-verde',
        in_array($e, ['cancelado', 'rechazada', 'cancelada'])             => 'badge-rojo',
        in_array($e, ['pendiente', 'borrador'])                           => 'badge-amarillo',
        default                                                            => 'badge-gris',
    };
    return '<span class="badge ' . $cls . '">' . esc($estado) . '</span>';
}
?>
</body>
</html>
