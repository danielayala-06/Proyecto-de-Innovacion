<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte — <?= esc($rango) ?></title>
<style>
@page { margin: 2cm 2.5cm; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 12px; }
body { font-family: DejaVu Sans, Arial, sans-serif; color: #1a1a2e; background: #fff; }

.page { padding: 1.2cm 2cm; }

/* ── Encabezado ──────────────────────────────────────────────────────────── */
.rpt-header { border-bottom: 3px solid #1a1a2e; padding-bottom: .7rem; margin-bottom: 1.2rem; overflow: hidden; }
.rpt-header .brand { font-size: 1.1rem; font-weight: 800; color: #1a1a2e; float: left; }
.rpt-header .brand span { color: #2d4a8a; }
.rpt-header .meta { float: right; text-align: right; font-size: .72rem; color: #555; }
.rpt-header .meta strong { display: block; font-size: .8rem; color: #1a1a2e; }
.clearfix::after { content: ''; display: table; clear: both; }

/* ── Títulos de sección ──────────────────────────────────────────────────── */
.sec-title {
    font-size: .62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #fff;
    background: #2d4a8a;
    padding: .3rem .7rem;
    margin: 1.2rem 0 .5rem;
}

/* ── KPI tabla compacta ──────────────────────────────────────────────────── */
.kpi-table { width: 100%; border-collapse: collapse; margin-bottom: .5rem; }
.kpi-table td { padding: .35rem .6rem; border: 1px solid #ddd; font-size: .78rem; }
.kpi-table .kpi-label { color: #555; width: 60%; }
.kpi-table .kpi-val   { font-weight: 700; color: #1a1a2e; text-align: right; }
.kpi-table tr:nth-child(even) td { background: #f5f5f5; }

/* ── Tablas de datos ─────────────────────────────────────────────────────── */
table { width: 100%; border-collapse: collapse; font-size: .73rem; }
thead { background: #2d4a8a; color: #fff; }
thead th { padding: .32rem .45rem; text-align: left; font-weight: 600; font-size: .65rem; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
tbody tr:nth-child(even) td { background: #f5f5f5; }
tbody td { padding: .28rem .45rem; border-bottom: 1px solid #e8e8e8; color: #222; }
tfoot td { font-weight: 700; background: #e8f4e8; padding: .32rem .45rem; border-top: 2px solid #2d4a8a; }

.num { text-align: right; }
.badge { display: inline; font-size: .6rem; padding: .08rem .35rem; border-radius: 2px; font-weight: 700; text-transform: uppercase; }
.badge-verde    { background: #d4edda; color: #155724; }
.badge-rojo     { background: #f8d7da; color: #721c24; }
.badge-gris     { background: #e2e3e5; color: #383d41; }
.badge-amarillo { background: #fff3cd; color: #856404; }

/* ── Pie de página ───────────────────────────────────────────────────────── */
.rpt-footer { margin-top: 1.5rem; border-top: 1px solid #ccc; padding-top: .5rem; font-size: .62rem; color: #999; overflow: hidden; }
.rpt-footer .fl { float: left; }
.rpt-footer .fr { float: right; }
</style>
</head>
<body>
<div class="page">

    <!-- Encabezado -->
    <div class="rpt-header clearfix">
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
    <table class="kpi-table">
        <tbody>
            <tr>
                <td class="kpi-label">Contratos creados en el período</td>
                <td class="kpi-val"><?= $kpi['contratos'] ?></td>
                <td class="kpi-label">Contratos activos (total)</td>
                <td class="kpi-val"><?= $kpi['activos'] ?></td>
            </tr>
            <tr>
                <td class="kpi-label">Ingresos recibidos en el período</td>
                <td class="kpi-val">S/ <?= number_format($kpi['ingresos'], 2, '.', ',') ?></td>
                <td class="kpi-label">Sesiones en el período</td>
                <td class="kpi-val"><?= $kpi['sesiones'] ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- ── Cotizaciones ─────────────────────────────────────────────────────── -->
    <?php if (isset($cotizaciones)): ?>
    <div class="sec-title">Cotizaciones</div>
    <?php if (empty($cotizaciones)): ?>
        <p style="color:#999;font-size:.75rem;margin:.4rem 0">Sin registros en el período.</p>
    <?php else: ?>
    <?php $totalCotiz = array_sum(array_column($cotizaciones, 'total')); ?>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Fecha</th><th>Institución</th>
                <th>Promoción</th><th>Grado</th><th>Estado</th><th class="num">Total (S/)</th>
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
        <p style="color:#999;font-size:.75rem;margin:.4rem 0">Sin registros en el período.</p>
    <?php else: ?>
    <?php
        $totalContratos = array_sum(array_column($contratos, 'total'));
        $totalAdelanto  = array_sum(array_column($contratos, 'adelanto'));
        $totalSaldo     = array_sum(array_column($contratos, 'saldo'));
    ?>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Fecha</th><th>Institución</th><th>Promoción</th>
                <th>Grado</th><th>Estado</th>
                <th class="num">Total (S/)</th><th class="num">Adelanto</th><th class="num">Saldo</th>
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
                <td class="num">S/ <?= number_format($totalAdelanto, 2) ?></td>
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
        <p style="color:#999;font-size:.75rem;margin:.4rem 0">Sin registros en el período.</p>
    <?php else: ?>
    <?php $totalPagos = array_sum(array_column($pagos, 'monto')); ?>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Fecha</th><th>Institución</th><th>Promoción</th>
                <th>Contrato</th><th>Forma de pago</th><th>Moneda</th><th class="num">Monto</th>
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
        <p style="color:#999;font-size:.75rem;margin:.4rem 0">Sin registros en el período.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Fecha y hora</th><th>Institución</th>
                <th>Promoción</th><th>Tipo</th><th>Estado</th>
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
            <tr><td colspan="6">Total: <?= count($sesiones) ?> sesiones</td></tr>
        </tfoot>
    </table>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Pie -->
    <div class="rpt-footer clearfix">
        <span class="fl">Ronceros Fotografía · Sistema de gestión</span>
        <span class="fr">Período: <?= esc($rango) ?></span>
    </div>

</div>

<?php
function _badgeEstado(?string $estado): string
{
    if (!$estado) return '<span class="badge badge-gris">—</span>';
    $e   = strtolower($estado);
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
