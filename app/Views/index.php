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
    <h3 class="fw-lighter">Inicio</h3>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Sesiones este mes</div>
                <div class="stat-value"><?= $sesionesEstesMes ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Contratos activos</div>
                <div class="stat-value"><?= $contratosActivos ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Clientes totales</div>
                <div class="stat-value"><?= $totalClientes ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Ingresos (S/)</div>
                <div class="stat-value"><?= number_format($ingresos, 0, '.', ',') ?></div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="card-title">Próximas sesiones aprobadas</div>
        <table class="table table-borderless">
            <thead>
            <tr>
                <th>Cliente</th>
                <th>Tipo / nombre</th>
                <th>Fecha</th>
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

</main>
<?= $footer ?>
