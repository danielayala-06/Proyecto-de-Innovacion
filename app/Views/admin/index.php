<?= $header ?>

<main class="main-content" id="main-content">
<div class="container">

    <!-- HEADER -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
        <p class="page-title" style="margin:0;">Formularios</p>
        <?php if (!empty($promoEscolares)): ?>
        <button id="btnNuevoFormulario"
                style="display:inline-flex;align-items:center;gap:.4rem;background:var(--accent);color:#fff;
                       border:none;border-radius:9px;padding:.45rem 1rem;font-size:.82rem;font-weight:600;cursor:pointer;">
            <i class="bi bi-plus-circle"></i> Nuevo formulario
        </button>
        <?php else: ?>
        <span title="No hay contratos disponibles para crear formularios"
              style="display:inline-flex;align-items:center;gap:.4rem;background:var(--bg-input);color:var(--text-muted);
                     border:1px dashed var(--border);border-radius:9px;padding:.45rem 1rem;font-size:.82rem;
                     font-weight:600;cursor:not-allowed;opacity:.6;">
            <i class="bi bi-plus-circle"></i> Nuevo formulario
        </span>
        <?php endif; ?>
    </div>

    <?php if (empty($promoEscolares)): ?>
    <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:10px;
                padding:.85rem 1.1rem;margin-bottom:1rem;display:flex;align-items:flex-start;gap:.65rem;font-size:.82rem;">
        <i class="bi bi-info-circle-fill" style="color:#f59e0b;margin-top:.1rem;flex-shrink:0;"></i>
        <div>
            Para crear formularios primero debes tener un <strong>contrato activo</strong> en el sistema.
            Los formularios están vinculados al contrato de cada promoción escolar.
        </div>
    </div>
    <?php endif; ?>

    <div class="cot-table-card">
        <?php if (empty($promociones)): ?>
            <div class="empty-state">
                <i class="bi bi-file-earmark-person" style="font-size:2rem;"></i>
                No hay formularios registrados aún.
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
                    <td style="white-space:nowrap;">
                        <a href="<?= base_url('admin/formularios/promocion/' . $p['id']) ?>"
                           style="color:var(--text-muted);font-size:.78rem;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;">
                            Ver <i class="bi bi-chevron-right"></i>
                        </a>
                        <button class="btn-eliminar-formulario"
                                data-id="<?= $p['id'] ?>"
                                data-nombre="<?= esc($p['nombre']) ?>"
                                style="background:none;border:none;cursor:pointer;color:var(--red-text,#e07070);font-size:.78rem;padding:.1rem .3rem;margin-left:.1rem;"
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
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

<!-- ══════ MODAL NUEVO FORMULARIO ══════════════════════════════════════════════ -->
<?php if (!empty($promoEscolares)): ?>
<div id="modalNuevoFormulario"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1060;
            align-items:center;justify-content:center;padding:1rem;">
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:14px;
                width:100%;max-width:500px;padding:1.5rem;box-shadow:0 8px 40px rgba(0,0,0,.35);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;">
            <h6 style="margin:0;font-size:.95rem;font-weight:700;">
                <i class="bi bi-file-earmark-person me-2" style="color:var(--accent);"></i>Nuevo formulario
            </h6>
            <button id="btnCerrarModal"
                    style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.1rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Contrato / Promoción (REQUERIDO) -->
        <div class="mb-3">
            <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.35rem;">
                Contrato asociado <span style="color:var(--red-text);">*</span>
            </label>
            <select id="ncPromoEscolar" class="form-select">
                <option value="">— Selecciona el contrato / promoción —</option>
                <?php foreach ($promoEscolares as $pe): ?>
                <option value="<?= $pe['id_promocion'] ?>"
                        data-nombre="<?= esc($pe['nombre']) ?> <?= esc($pe['grado']) ?>"
                        data-colegio="<?= $pe['id_colegio'] ?? '' ?>"
                        data-colegio-nombre="<?= esc($pe['nombre_colegio']) ?>">
                    <?= esc($pe['nombre_colegio']) ?> — <?= esc($pe['nombre']) ?> <?= esc($pe['grado']) ?>
                    (Contrato #<?= str_pad($pe['id_contrato'], 4, '0', STR_PAD_LEFT) ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <div style="font-size:.73rem;color:var(--text-muted);margin-top:.3rem;">
                Solo se muestran contratos activos con al menos una promoción vinculada.
            </div>
        </div>

        <!-- Nombre del grupo (se auto-completa) -->
        <div class="mb-3">
            <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.35rem;">
                Nombre descriptivo <span style="color:var(--red-text);">*</span>
            </label>
            <input type="text" id="ncNombre" class="form-control" maxlength="150"
                   placeholder="Se completará al seleccionar el contrato">
        </div>

        <!-- Nivel (opcional, se auto-completa) -->
        <div class="mb-3">
            <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.35rem;">
                Nivel / Grado <span style="font-weight:400;color:var(--text-muted);">(opcional)</span>
            </label>
            <input type="text" id="ncNivel" class="form-control" maxlength="80"
                   placeholder="Ej: Primaria 6to C">
        </div>

        <div style="display:flex;justify-content:flex-end;gap:.6rem;margin-top:1.2rem;">
            <button id="btnCancelarModal"
                    style="background:var(--bg-input);color:var(--text-primary);border:1px solid var(--border);
                           border-radius:8px;padding:.4rem .9rem;font-size:.82rem;font-weight:600;cursor:pointer;">
                Cancelar
            </button>
            <button id="btnGuardarFormulario"
                    style="background:var(--accent);color:#fff;border:none;border-radius:8px;
                           padding:.4rem 1rem;font-size:.82rem;font-weight:600;cursor:pointer;">
                <i class="bi bi-check-circle me-1"></i> Crear formulario
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const BASE_URL_ADM = "<?= base_url('') ?>";

// ── Modal nuevo formulario ────────────────────────────────────────────────────
(function () {
    const btnAbrir  = document.getElementById('btnNuevoFormulario');
    const modal     = document.getElementById('modalNuevoFormulario');
    if (!btnAbrir || !modal) return;

    const btnCerrar1 = document.getElementById('btnCerrarModal');
    const btnCerrar2 = document.getElementById('btnCancelarModal');
    const btnGuardar = document.getElementById('btnGuardarFormulario');
    const selContrato = document.getElementById('ncPromoEscolar');
    const inputNombre = document.getElementById('ncNombre');
    const inputNivel  = document.getElementById('ncNivel');

    function abrir() {
        selContrato.value  = '';
        inputNombre.value  = '';
        inputNivel.value   = '';
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Crear formulario';
        modal.style.display = 'flex';
        selContrato.focus();
    }

    function cerrar() { modal.style.display = 'none'; }

    btnAbrir.addEventListener('click', abrir);
    btnCerrar1.addEventListener('click', cerrar);
    btnCerrar2.addEventListener('click', cerrar);
    modal.addEventListener('click', function(e) { if (e.target === modal) cerrar(); });

    // Auto-completar nombre y nivel al seleccionar contrato
    selContrato.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (this.value && opt.dataset.nombre) {
            if (!inputNombre.value.trim()) {
                inputNombre.value = opt.dataset.colegioNombre + ' — ' + opt.dataset.nombre;
            }
            if (!inputNivel.value.trim()) {
                inputNivel.value = opt.dataset.nombre;
            }
        }
    });

    btnGuardar.addEventListener('click', async function () {
        const idEscolar = selContrato.value;
        const nombre    = inputNombre.value.trim();
        const nivel     = inputNivel.value.trim();
        const opt       = selContrato.options[selContrato.selectedIndex];

        if (!idEscolar) { selContrato.focus(); return; }
        if (!nombre)    { inputNombre.focus(); return; }

        const body = {
            nombre,
            nivel,
            id_promocion_escolar: parseInt(idEscolar),
            id_colegio:           opt.dataset.colegio ? parseInt(opt.dataset.colegio) : undefined,
        };

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Creando...';

        try {
            const r    = await fetch(BASE_URL_ADM + 'admin/formularios', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(body),
            });
            const data = await r.json();

            if (data.ok && data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert(data.error || 'No se pudo crear el formulario.');
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Crear formulario';
            }
        } catch {
            alert('Error de conexión.');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Crear formulario';
        }
    });
})();

// ── Vincular a sesiones ───────────────────────────────────────────────────────
document.querySelectorAll('tr[data-prom-id]').forEach(function(row) {
    function attachVincular() {
        var btn = row.querySelector('.btn-vincular');
        if (!btn) return;
        btn.addEventListener('click', function() {
            var sel       = row.querySelector('.vincular-select');
            var idEscolar = sel ? sel.value : '';
            var promId    = row.dataset.promId;

            fetch(BASE_URL_ADM + 'admin/formularios/vincular/' + promId, {
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

// ── Eliminar formulario ───────────────────────────────────────────────────────
document.querySelectorAll('.btn-eliminar-formulario').forEach(function(btn) {
    btn.addEventListener('click', async function() {
        const id     = this.dataset.id;
        const nombre = this.dataset.nombre;
        if (!confirm('¿Eliminar el formulario "' + nombre + '"?\n\nSe eliminarán todos los alumnos y respuestas asociadas. Esta acción no se puede deshacer.')) return;

        try {
            const r    = await fetch(BASE_URL_ADM + 'admin/formularios/promocion/' + id, {
                method:  'DELETE',
                headers: { 'Content-Type': 'application/json' },
            });
            const data = await r.json();
            if (data.ok) {
                this.closest('tr').remove();
            } else {
                alert(data.error || 'No se pudo eliminar.');
            }
        } catch {
            alert('Error de conexión.');
        }
    });
});
</script>

<?= $footer ?>
