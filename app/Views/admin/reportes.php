<?php /* Vista generador de reportes */ ?>
<?= $header ?>

<style>
/* ── Layout ──────────────────────────────────────────────────────────────── */
.rpt-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 860px) {
    .rpt-layout { grid-template-columns: 1fr; }
}

/* ── Panel config ────────────────────────────────────────────────────────── */
.rpt-config {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1.25rem;
    position: sticky;
    top: 1rem;
}
.rpt-config h6 {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--text-secondary);
    margin: 0 0 .9rem;
}
.rpt-section { margin-bottom: 1.1rem; }
.rpt-section label.section-label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: .45rem;
}
.rpt-months {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .5rem;
}
.rpt-months input[type=month] {
    width: 100%;
    padding: .38rem .55rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--bg-body);
    color: var(--text-primary);
    font-size: .82rem;
}
.rpt-months .month-hint {
    font-size: .7rem;
    color: var(--text-muted);
    margin-top: .15rem;
}

/* ── Checkboxes módulos ──────────────────────────────────────────────────── */
.mod-list { display: flex; flex-direction: column; gap: .4rem; }
.mod-item {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .45rem .6rem;
    border-radius: 7px;
    cursor: pointer;
    transition: background .15s;
    user-select: none;
}
.mod-item:hover { background: var(--bg-hover, rgba(128,128,128,.08)); }
.mod-item input[type=checkbox] { accent-color: var(--accent); width: 15px; height: 15px; cursor: pointer; }
.mod-item .mod-icon { font-size: .95rem; width: 18px; text-align: center; }
.mod-item .mod-name { font-size: .83rem; font-weight: 500; color: var(--text-primary); }

/* ── Botones acción ──────────────────────────────────────────────────────── */
.rpt-actions { display: flex; flex-direction: column; gap: .5rem; margin-top: 1rem; }
.rpt-actions .btn { font-size: .83rem; }
.btn-preview { background: var(--accent); color: #fff; border: none; }
.btn-preview:hover { filter: brightness(1.1); color: #fff; }

/* ── Panel preview ───────────────────────────────────────────────────────── */
.rpt-preview {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    min-height: 260px;
}
.rpt-preview-head {
    padding: .75rem 1.1rem;
    border-bottom: 1px solid var(--border);
    font-size: .82rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .04em;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
}
.rpt-preview-body { padding: 1.1rem; }

/* ── Estado vacío / cargando ─────────────────────────────────────────────── */
.rpt-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .65rem;
    padding: 3rem 1rem;
    color: var(--text-muted);
    font-size: .85rem;
}
.rpt-empty i { font-size: 2rem; opacity: .4; }

/* ── Tarjetas de módulo en preview ──────────────────────────────────────── */
.mod-cards { display: grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap: .85rem; }
.mod-card {
    background: var(--bg-body);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: .9rem 1rem;
}
.mod-card-title {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--accent);
    margin-bottom: .65rem;
    display: flex;
    align-items: center;
    gap: .35rem;
}
.mod-kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .4rem .6rem; }
.mod-kpi-item .kpi-label { font-size: .7rem; color: var(--text-muted); }
.mod-kpi-item .kpi-val   { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
.mod-stat { font-size: .8rem; color: var(--text-secondary); line-height: 1.7; }
.mod-stat strong { color: var(--text-primary); }
.rango-badge {
    font-size: .72rem;
    background: var(--accent);
    color: #fff;
    border-radius: 20px;
    padding: .15rem .65rem;
    font-weight: 600;
}

/* ── Spinner ─────────────────────────────────────────────────────────────── */
.spinner {
    display: inline-block;
    width: 22px; height: 22px;
    border: 3px solid var(--border);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<main class="main-content" id="main-content">
<div class="container">

    <div class="d-flex align-items-center gap-2 mb-3">
        <h3 class="fw-lighter mb-0">Generador de reportes</h3>
    </div>

    <div class="rpt-layout">

        <!-- ── Panel configuración ──────────────────────────────────────────── -->
        <aside class="rpt-config">
            <h6><i class="bi bi-sliders me-1"></i>Configuración</h6>

            <!-- Período -->
            <div class="rpt-section">
                <label class="section-label">Período</label>
                <div class="rpt-months">
                    <div>
                        <input type="month" id="rptDesde" value="<?= date('Y-m', strtotime('-1 month')) ?>">
                        <div class="month-hint">Desde</div>
                    </div>
                    <div>
                        <input type="month" id="rptHasta" value="<?= date('Y-m') ?>">
                        <div class="month-hint">Hasta</div>
                    </div>
                </div>
            </div>

            <!-- Módulos -->
            <div class="rpt-section">
                <label class="section-label">Módulos</label>
                <div class="mod-list">
                    <label class="mod-item">
                        <input type="checkbox" name="modulo" value="kpi" checked>
                        <span class="mod-icon">📊</span>
                        <span class="mod-name">Resumen KPI</span>
                    </label>
                    <label class="mod-item">
                        <input type="checkbox" name="modulo" value="cotizaciones" checked>
                        <span class="mod-icon">📄</span>
                        <span class="mod-name">Cotizaciones</span>
                    </label>
                    <label class="mod-item">
                        <input type="checkbox" name="modulo" value="contratos" checked>
                        <span class="mod-icon">📋</span>
                        <span class="mod-name">Contratos</span>
                    </label>
                    <label class="mod-item">
                        <input type="checkbox" name="modulo" value="pagos" checked>
                        <span class="mod-icon">💰</span>
                        <span class="mod-name">Pagos</span>
                    </label>
                    <label class="mod-item">
                        <input type="checkbox" name="modulo" value="sesiones" checked>
                        <span class="mod-icon">📷</span>
                        <span class="mod-name">Sesiones fotográficas</span>
                    </label>
                </div>
            </div>

            <!-- Acciones -->
            <div class="rpt-actions">
                <button class="btn btn-preview rounded-2 d-flex align-items-center justify-content-center gap-2"
                        id="btnPreview" onclick="generarPreview()">
                    <i class="bi bi-eye"></i> Ver preview
                </button>
                <button class="btn btn-success rounded-2 d-flex align-items-center justify-content-center gap-2"
                        id="btnExcel" onclick="descargarExcel()">
                    <i class="bi bi-file-earmark-excel-fill"></i> Descargar Excel
                </button>
                <button class="btn btn-outline-secondary rounded-2 d-flex align-items-center justify-content-center gap-2"
                        id="btnPrint" onclick="abrirImpresion()">
                    <i class="bi bi-printer"></i> Imprimir / PDF
                </button>
            </div>
        </aside>

        <!-- ── Panel preview ─────────────────────────────────────────────────── -->
        <section class="rpt-preview">
            <div class="rpt-preview-head">
                <span><i class="bi bi-eye me-1"></i>Vista previa</span>
                <span id="previewBadge" class="rango-badge" style="display:none"></span>
            </div>
            <div class="rpt-preview-body" id="previewBody">
                <div class="rpt-empty">
                    <i class="bi bi-bar-chart-steps"></i>
                    <span>Selecciona el período y módulos,<br>luego pulsa <strong>Ver preview</strong>.</span>
                </div>
            </div>
        </section>

    </div>
</div>
</main>

<script>
const BASE_URL    = "<?= base_url('') ?>";
const CSRF_TOKEN  = "<?= csrf_hash() ?>";

/* ── Helpers ────────────────────────────────────────────────────────────── */
function getConfig() {
    const desde   = document.getElementById('rptDesde').value;
    const hasta   = document.getElementById('rptHasta').value;
    const modulos = [...document.querySelectorAll('input[name=modulo]:checked')].map(c => c.value);
    return { desde, hasta, modulos };
}

function buildUrl(endpoint) {
    const { desde, hasta, modulos } = getConfig();
    const params = new URLSearchParams({ desde, hasta });
    modulos.forEach(m => params.append('modulos[]', m));
    return BASE_URL + 'index.php/admin/reporte/' + endpoint + '?' + params.toString();
}

function descargarExcel()   { window.location.href = buildUrl('exportar'); }
function abrirImpresion()   { window.open(buildUrl('imprimir'), '_blank'); }

/* ── Preview ────────────────────────────────────────────────────────────── */
async function generarPreview() {
    const cfg = getConfig();
    if (!cfg.desde || !cfg.hasta) return;
    if (cfg.modulos.length === 0) {
        alert('Selecciona al menos un módulo.');
        return;
    }

    const body = document.getElementById('previewBody');
    body.innerHTML = '<div class="rpt-empty"><div class="spinner"></div><span>Calculando…</span></div>';
    document.getElementById('previewBadge').style.display = 'none';

    try {
        const res = await fetch(BASE_URL + 'index.php/admin/reporte/preview', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify(cfg),
        });
        const json = await res.json();
        if (json.status !== 'success') throw new Error('Error del servidor');
        renderPreview(json.data);
    } catch (e) {
        body.innerHTML = '<div class="rpt-empty"><i class="bi bi-exclamation-triangle"></i><span>Error al obtener datos. Inténtalo de nuevo.</span></div>';
    }
}

/* ── Render ─────────────────────────────────────────────────────────────── */
function renderPreview(data) {
    const body  = document.getElementById('previewBody');
    const badge = document.getElementById('previewBadge');
    badge.textContent = data.rango;
    badge.style.display = '';

    const mods = data.modulos;
    let html = '<div class="mod-cards">';

    if (mods.kpi) {
        const k = mods.kpi;
        html += `
        <div class="mod-card">
            <div class="mod-card-title"><i class="bi bi-graph-up-arrow"></i> Resumen KPI</div>
            <div class="mod-kpi-grid">
                <div class="mod-kpi-item">
                    <div class="kpi-label">Contratos creados</div>
                    <div class="kpi-val">${k.contratos}</div>
                </div>
                <div class="mod-kpi-item">
                    <div class="kpi-label">Ingresos período</div>
                    <div class="kpi-val">${fmtS(k.ingresos)}</div>
                </div>
                <div class="mod-kpi-item">
                    <div class="kpi-label">Contratos activos</div>
                    <div class="kpi-val">${k.activos}</div>
                </div>
                <div class="mod-kpi-item">
                    <div class="kpi-label">Sesiones</div>
                    <div class="kpi-val">${k.sesiones}</div>
                </div>
            </div>
        </div>`;
    }

    if (mods.cotizaciones) {
        const c = mods.cotizaciones;
        const est = (c.estados || []).map(e => `<strong>${e.total}</strong> ${e.estado}`).join(' · ') || '—';
        html += `
        <div class="mod-card">
            <div class="mod-card-title"><i class="bi bi-file-earmark-text"></i> Cotizaciones</div>
            <div class="mod-stat">Total: <strong>${c.total}</strong><br>${est}</div>
        </div>`;
    }

    if (mods.contratos) {
        const c = mods.contratos;
        html += `
        <div class="mod-card">
            <div class="mod-card-title"><i class="bi bi-file-earmark-check"></i> Contratos</div>
            <div class="mod-stat">
                Registros: <strong>${c.total}</strong><br>
                Valor total: <strong>${fmtS(c.valor)}</strong>
            </div>
        </div>`;
    }

    if (mods.pagos) {
        const p = mods.pagos;
        html += `
        <div class="mod-card">
            <div class="mod-card-title"><i class="bi bi-cash-stack"></i> Pagos</div>
            <div class="mod-stat">
                Transacciones: <strong>${p.total}</strong><br>
                Monto recibido: <strong>${fmtS(p.monto)}</strong>
            </div>
        </div>`;
    }

    if (mods.sesiones) {
        const s = mods.sesiones;
        const est = (s.estados || []).map(e => `<strong>${e.total}</strong> ${e.estado}`).join(' · ') || '—';
        html += `
        <div class="mod-card">
            <div class="mod-card-title"><i class="bi bi-camera"></i> Sesiones</div>
            <div class="mod-stat">Total: <strong>${s.total}</strong><br>${est}</div>
        </div>`;
    }

    html += '</div>';
    body.innerHTML = html;
}

function fmtS(n) {
    return 'S/ ' + Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<?= $footer ?>
