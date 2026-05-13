<?php
function _fmtFecha(?string $f): string {
    if (!$f) return '—';
    $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    $d = new DateTime($f);
    return $d->format('d') . ' ' . $meses[(int)$d->format('n') - 1];
}
?>
<?= $header ?>
<main class="main-content" id="main-content">
    <div class="container">
            
        <h3 class="fw-lighter">Inicio</h3>

        <!-- STAT CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card-v">
                    <div class="stat-icon gold"><i class="bi bi-camera-fill"></i></div>
                    <div class="stat-label">Sesiones este mes</div>
                    <div class="stat-value"><?= $sesionesEstesMes??0 ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-v">
                    <div class="stat-icon green"><i class="bi bi-file-earmark-check-fill"></i></div>
                    <div class="stat-label">Contratos activos</div>
                    <div class="stat-value"><?= $contratosActivos??0 ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-v">
                    <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-label">Clientes totales</div>
                    <div class="stat-value"><?= $totalClientes??0 ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-v">
                    <div class="stat-icon amber"><i class="bi bi-graph-up-arrow"></i></div>
                    <div class="stat-label">Ingresos (S/)</div>
                    <div class="stat-value"><?= number_format($ingresos??0, 0, '.', ',') ?></div>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-card">
            <div class="card-title">Próximas sesiones aprobadas</div>
            <table class="table table-borderless">
                <thead>
                <tr>
                    <th class="text-uppercase">Cliente</th>
                    <th class="text-uppercase">Tipo / nombre</th>
                    <th class="text-uppercase">Fecha</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($proximasSesiones)): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;color:var(--text-muted);padding:1.5rem;">
                            No hay sesiones próximas aprobadas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($proximasSesiones as $s): ?>
                        <tr>
                            <td><?= esc($s['cliente']) ?></td>
                            <td><?= esc($s['tipo'] ?? '—') ?></td>
                            <td><?= _fmtFecha($s['fecha']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?= $footer ?>
