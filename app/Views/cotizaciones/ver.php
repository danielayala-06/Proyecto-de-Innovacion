<?= $header ?>
<style>
.cv-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 900px) { .cv-grid { grid-template-columns: 1fr; } }

.cv-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 22px 24px;
}
.cv-card-title {
    font-size: .82rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.cv-section { margin-bottom: 20px; }
.cv-section:last-child { margin-bottom: 0; }
.cv-section-title {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--text-muted);
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border-color);
}
.cv-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    font-size: .83rem;
    padding: 4px 0;
    gap: 12px;
}
.cv-row span { color: var(--text-muted); font-size: .8rem; }
.cv-row strong { text-align: right; }

.cv-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0 0;
    border-top: 1px solid var(--border-color);
    font-size: .92rem;
    font-weight: 700;
    margin-top: 8px;
}
.cv-total-row strong { color: var(--accent); font-size: 1.1rem; }

.cv-items-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.cv-items-table thead th {
    font-size: .71rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 5px 6px;
    border-bottom: 1px solid var(--border-color);
}
.cv-items-table tbody td {
    padding: 7px 6px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: top;
    line-height: 1.4;
}
.cv-items-table tbody tr:last-child td { border-bottom: none; }

.cv-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .82rem;
    color: var(--text-muted);
    text-decoration: none;
    margin-bottom: 18px;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    transition: color .15s;
}
.cv-back:hover { color: var(--text-primary); }

.cv-estado-badge {
    display: inline-block;
    padding: .25rem .65rem;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
}

.cv-btn-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: 100%;
    padding: 9px;
    border-radius: 7px;
    font-size: .85rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: opacity .15s;
}
.cv-btn-action:hover { opacity: .85; }
.cv-btn-primary   { background: var(--accent); color: #fff; }
.cv-btn-secondary { background: var(--border-color); color: var(--text-primary); }

.cv-alert-row {
    display: flex;
    align-items: flex-start;
    gap: .4rem;
    padding: .35rem .6rem;
    border-radius: 6px;
    font-size: .76rem;
    margin-bottom: .25rem;
    line-height: 1.4;
}
.cv-prom-card {
    background: var(--bg-hover);
    border-radius: 8px;
    padding: 12px 14px;
}
</style>

<main id="main-content">
    <div class="container" style="max-width:1100px;">

        <button class="cv-back" onclick="history.length > 1 ? history.back() : (location.href = '<?= base_url('/cotizaciones') ?>')">
            <i class="bi bi-arrow-left"></i> Volver
        </button>

        <p class="page-title" style="margin-bottom:4px;">Detalle de cotización</p>
        <p id="cvSubtitle" class="fw-bold" style="font-size:.85rem;color:var(--text-muted);margin-bottom:22px;">Cargando…</p>

        <div class="cv-grid">

            <!-- ── COLUMNA IZQUIERDA ──────────────────────────────── -->
            <div class="d-flex flex-column gap-4">

                <!-- Cliente + colegio -->
                <div class="cv-card">
                    <div class="cv-card-title"><i class="bi bi-person-lines-fill"></i> Cliente</div>
                    <div class="shadow p-3 bg-body-tertiary rounded">
                        <div id="cvCliente">
                            <div class="placeholder-glow d-flex flex-column gap-2">
                                <span class="placeholder col-7 rounded"></span>
                                <span class="placeholder col-5 rounded"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ítems -->
                <div class="cv-card">
                    <div class="cv-card-title"><i class="bi bi-list-ul"></i> Ítems cotizados</div>
                    <div class="shadow p-3 bg-body-tertiary rounded">
                        <div id="cvItems">
                            <div class="placeholder-glow d-flex flex-column gap-2">
                                <span class="placeholder col-12 rounded"></span>
                                <span class="placeholder col-10 rounded"></span>
                                <span class="placeholder col-9 rounded"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promoción (si aplica) -->
                <div class="cv-card" id="cvPromCard" style="display:none;">
                    <div class="cv-card-title"><i class="bi bi-mortarboard"></i> Promoción escolar</div>
                    <div class="shadow p-3 bg-body-tertiary rounded">
                        <div class="cv-prom-card" id="cvProm"></div>
                    </div>
                </div>

            </div>

            <!-- ── COLUMNA DERECHA (sticky) ───────────────────────── -->
            <div class="cv-card d-flex flex-column gap-2" style="position:sticky;top:80px;">

                <!-- Estado -->
                <div class="cv-card-title" style="margin-bottom:8px;"><i class="bi bi-info-circle"></i> Estado</div>
                <div class="shadow p-3 bg-body-tertiary rounded d-flex flex-column gap-3">

                    <div>
                        <div id="cvEstado"></div>
                    </div>

                    <!-- Resumen económico -->
                    <div>
                        <div class="cv-section-title">Resumen</div>
                        <div class="cv-row"><span>Total cotización</span><strong id="cvTotal">—</strong></div>
                        <div class="cv-row"><span>Fecha</span><strong id="cvFecha">—</strong></div>
                        <div class="cv-row"><span>Registrado por</span><strong id="cvUsuario">—</strong></div>
                    </div>

                    <!-- Observaciones -->
                    <div id="cvObsWrap" style="display:none;">
                        <div class="cv-section-title">Observaciones</div>
                        <p id="cvObs" style="font-size:.8rem;color:var(--text-muted);margin:0;"></p>
                    </div>

                </div>

                <!-- Acciones (fuera del fondito para que resalten) -->
                <div id="cvAcciones" class="d-flex flex-column gap-2"></div>

            </div>

        </div>
    </div>
</main>

<script>const BASE_URL = "<?= base_url('') ?>";const COT_ID = <?= (int) $id_cotizacion ?>;</script>
<script type="module" src="<?= base_url('js/modules/cotizaciones/cotizacionVer.js') ?>"></script>

<?= $footer ?>
