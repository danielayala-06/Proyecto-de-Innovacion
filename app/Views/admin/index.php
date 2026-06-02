<?= $header ?>

<main class="main-content" id="main-content">
<div class="container">

    <p class="page-title">Formularios</p>

    <div class="cot-table-card">
        <?php if (empty($promociones)): ?>
            <div class="empty-state">
                <i class="bi bi-file-earmark-person" style="font-size:2rem;"></i>
                No hay promociones registradas aún.
            </div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-sm" style="font-size:.84rem;">
            <thead>
                <tr>
                    <th>Colegio</th>
                    <th>Promoción</th>
                    <th>Alumnos</th>
                    <th>Progreso</th>
                    <th>Cuadros</th>
                    <th>Anuarios</th>
                    <th>Estado</th>
                    <th>Sesiones</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promociones as $p): ?>
                <?php
                    $pct = $p['total_alumnos'] > 0
                        ? round(($p['completados'] / $p['total_alumnos']) * 100)
                        : 0;
                    $cuadrosLibres  = max(0, (int)$p['cuadros_total']  - (int)$p['cuadros_usados']);
                    $anuariosLibres = max(0, (int)$p['anuarios_total'] - (int)$p['anuarios_usados']);
                ?>
                <tr data-prom-id="<?= $p['id'] ?>">
                    <td><?= esc($p['nombre_colegio'] ?? '—') ?></td>
                    <td>
                        <span style="font-weight:600;"><?= esc($p['nombre']) ?></span>
                        <?php if ($p['nivel']): ?>
                            <br><small style="color:var(--text-muted);"><?= esc($p['nivel']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['total_alumnos'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;min-width:70px;">
                                <div style="height:100%;width:<?= $pct ?>%;background:var(--accent);border-radius:3px;transition:width .4s;"></div>
                            </div>
                            <span style="font-size:.73rem;color:var(--text-muted);"><?= $pct ?>%</span>
                        </div>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px;"><?= $p['completados'] ?>/<?= $p['total_alumnos'] ?></div>
                    </td>
                    <td><?= $cuadrosLibres ?>/<?= (int)$p['cuadros_total'] ?></td>
                    <td><?= $anuariosLibres ?>/<?= (int)$p['anuarios_total'] ?></td>
                    <td>
                        <span class="<?= $p['activa'] ? 'badge-aprobada' : 'badge-pendiente' ?>">
                            <?= $p['activa'] ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </td>
                    <td class="sesiones-cell">
                        <?php if ($p['sesiones_link']): ?>
                            <a href="<?= esc($p['sesiones_link']) ?>"
                               style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:.3rem;">
                                <i class="bi bi-camera"></i> Sesiones →
                            </a>
                        <?php else: ?>
                            <div style="display:flex;align-items:center;gap:.4rem;">
                                <select class="vincular-select filter-select" style="height:28px;font-size:.72rem;padding:0 .4rem;min-width:150px;">
                                    <option value="">Vincular a sesiones...</option>
                                    <?php foreach ($promoEscolares as $pe): ?>
                                    <option value="<?= $pe['id_promocion'] ?>">
                                        <?= esc($pe['nombre_colegio']) ?> — <?= esc($pe['nombre']) ?> <?= esc($pe['grado']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn-vincular"
                                        style="background:var(--accent);color:#fff;border:none;border-radius:6px;padding:.2rem .55rem;font-size:.75rem;font-weight:600;cursor:pointer;">
                                    ✓
                                </button>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= base_url('admin/formularios/promocion/' . $p['id']) ?>"
                           style="color:var(--text-muted);font-size:.78rem;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;">
                            Ver <i class="bi bi-chevron-right"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

</div>
</main>

<script>
document.querySelectorAll('tr[data-prom-id]').forEach(function(row) {
    function attachVincular() {
        var btn = row.querySelector('.btn-vincular');
        if (!btn) return;
        btn.addEventListener('click', function() {
            var sel      = row.querySelector('.vincular-select');
            var idEscolar = sel ? sel.value : '';
            var promId    = row.dataset.promId;

            fetch('<?= base_url('admin/formularios/vincular') ?>/' + promId, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ id_promocion_escolar: idEscolar || null }),
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.ok) { alert('Error: ' + (res.error || 'No se pudo vincular.')); return; }
                var cell = row.querySelector('.sesiones-cell');
                if (res.sesiones_link) {
                    cell.innerHTML = '<a href="' + res.sesiones_link + '" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:.3rem;"><i class="bi bi-camera"></i> Sesiones →</a>';
                } else {
                    cell.innerHTML = '<span style="font-size:.75rem;color:var(--text-muted);">Sin vincular</span>';
                    attachVincular();
                }
            })
            .catch(function() { alert('Error de red al vincular.'); });
        });
    }
    attachVincular();
});
</script>

<?= $footer ?>
