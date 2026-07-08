<?php
function _fmtFecha(?string $f): string {
    if (!$f) return '—';
    $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    $d = new DateTime($f);
    return $d->format('d') . ' ' . $meses[(int)$d->format('n') - 1];
}
?>
<?= $header ?>

<style>
.chart-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.chart-card .chart-title {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: .75rem 1.1rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: .4rem;
}
.chart-card .chart-title i { color: var(--accent); }
.chart-body { padding: 1rem 1.1rem 1.1rem; }
.chart-wrap { position: relative; }
</style>

<main class="main-content" id="main-content">
    <div class="container">

        <h3 class="fw-lighter mb-3">Inicio</h3>

        <!-- KPI CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl">
                <div class="stat-card-v">
                    <div class="stat-icon blue"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <div class="stat-label">Cotizaciones</div>
                    <div class="stat-value"><?= $cotizaciones ?? 0 ?></div>
                </div>
            </div>
            <div class="col-6 col-xl">
                <div class="stat-card-v">
                    <div class="stat-icon green"><i class="bi bi-file-earmark-check-fill"></i></div>
                    <div class="stat-label">Contratos activos</div>
                    <div class="stat-value"><?= $contratosActivos ?? 0 ?></div>
                </div>
            </div>
            <div class="col-6 col-xl">
                <div class="stat-card-v">
                    <div class="stat-icon amber"><i class="bi bi-megaphone-fill"></i></div>
                    <div class="stat-label">Promociones activas</div>
                    <div class="stat-value"><?= $promocioneActivas ?? 0 ?></div>
                </div>
            </div>
            <div class="col-6 col-xl">
                <div class="stat-card-v">
                    <div class="stat-icon gold"><i class="bi bi-camera-fill"></i></div>
                    <div class="stat-label">Sesiones este mes</div>
                    <div class="stat-value"><?= $sesionesEstesMes ?? 0 ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="stat-card-v">
                    <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
                    <div class="stat-label">Ingresos totales (S/)</div>
                    <div class="stat-value"><?= number_format($ingresos ?? 0, 0, '.', ',') ?></div>
                </div>
            </div>
        </div>

        <!-- ROW 1: Cotizaciones por mes + Estado cotizaciones -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-8">
                <div class="chart-card h-100">
                    <div class="chart-title">
                        <i class="bi bi-bar-chart-fill"></i>
                        Cotizaciones por mes
                    </div>
                    <div class="chart-body">
                        <div class="chart-wrap" style="height:240px">
                            <canvas id="chartCotizMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="chart-card h-100">
                    <div class="chart-title">
                        <i class="bi bi-pie-chart-fill"></i>
                        Estado de cotizaciones
                    </div>
                    <div class="chart-body">
                        <div class="chart-wrap" style="height:240px">
                            <canvas id="chartEstadoCotiz"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2: Contratos por mes + Estado contratos -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-4">
                <div class="chart-card h-100">
                    <div class="chart-title">
                        <i class="bi bi-pie-chart-fill"></i>
                        Estado de contratos
                    </div>
                    <div class="chart-body">
                        <div class="chart-wrap" style="height:240px">
                            <canvas id="chartEstadoContratos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-8">
                <div class="chart-card h-100">
                    <div class="chart-title">
                        <i class="bi bi-graph-up"></i>
                        Contratos por mes
                    </div>
                    <div class="chart-body">
                        <div class="chart-wrap" style="height:240px">
                            <canvas id="chartContratosMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 3: Sesiones por mes + Estado sesiones -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-8">
                <div class="chart-card h-100">
                    <div class="chart-title">
                        <i class="bi bi-graph-up"></i>
                        Sesiones por mes
                    </div>
                    <div class="chart-body">
                        <div class="chart-wrap" style="height:240px">
                            <canvas id="chartSesionesMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="chart-card h-100">
                    <div class="chart-title">
                        <i class="bi bi-pie-chart-fill"></i>
                        Estado de sesiones
                    </div>
                    <div class="chart-body">
                        <div class="chart-wrap" style="height:240px">
                            <canvas id="chartEstadoSesiones"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 4: Productos más vendidos + Valor por institución -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
                <div class="chart-card h-100">
                    <div class="chart-title">
                        <i class="bi bi-award-fill"></i>
                        Productos más vendidos
                    </div>
                    <div class="chart-body">
                        <div class="chart-wrap" style="height:240px">
                            <canvas id="chartProductos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="chart-card h-100">
                    <div class="chart-title">
                        <i class="bi bi-building-fill"></i>
                        Valor contratado por institución (S/)
                    </div>
                    <div class="chart-body">
                        <div class="chart-wrap" style="height:240px">
                            <canvas id="chartInstitucion"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRÓXIMAS SESIONES -->
        <div class="table-card mb-4">
            <div class="card-title">
                <i class="bi bi-calendar-event-fill me-1"></i>
                Próximas sesiones programadas
            </div>
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th class="text-uppercase">Institución</th>
                        <th class="text-uppercase">Tipo</th>
                        <th class="text-uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($proximasSesiones)): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;color:var(--text-muted);padding:1.5rem;">
                            No hay sesiones próximas programadas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($proximasSesiones as $s): ?>
                        <tr>
                            <td><?= esc($s['cliente']) ?></td>
                            <td><?= esc(ucfirst($s['tipo'] ?? '—')) ?></td>
                            <td><?= _fmtFecha($s['fecha']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="<?= base_url('js/modules/dashboard/dashboard.js') ?>"></script>

<?= $footer ?>
