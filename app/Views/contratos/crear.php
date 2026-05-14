<?= $header ?>
<style>
/* ── Layout ──────────────────────────────────────────────────────────────── */
.cc-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 900px) {
    .cc-grid { grid-template-columns: 1fr; }
}

/* ── Cards ───────────────────────────────────────────────────────────────── */
.cc-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 22px 24px;
}
.cc-card-title {
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

/* ── Preview sections ────────────────────────────────────────────────────── */
.cc-section {
    margin-bottom: 20px;
}
.cc-section:last-child {
    margin-bottom: 0;
}
.cc-section-title {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--text-muted);
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border-color);
}
.cc-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    font-size: .83rem;
    padding: 4px 0;
    gap: 12px;
}
.cc-row span { color: var(--text-muted); }
.cc-row strong { text-align: right; }

.cc-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0 0;
    border-top: 1px solid var(--border-color);
    font-size: .9rem;
    font-weight: 600;
    margin-top: 12px;
}
.cc-total-row strong { color: var(--accent); font-size: 1rem; }

/* ── Items table ─────────────────────────────────────────────────────────── */
.cc-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .8rem;
}
.cc-items-table thead th {
    font-size: .72rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 5px 6px;
    border-bottom: 1px solid var(--border-color);
}
.cc-items-table tbody td {
    padding: 7px 6px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: top;
    line-height: 1.4;
}
.cc-items-table tbody tr:last-child td {
    border-bottom: none;
}

/* ── Form ────────────────────────────────────────────────────────────────── */
.cc-form-group {
    margin-bottom: 16px;
}
.cc-form-group label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 5px;
}
.cc-form-group input,
.cc-form-group select,
.cc-form-group textarea {
    width: 100%;
    background: var(--bg-input);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 8px 10px;
    font-size: .84rem;
    color: var(--text-primary);
    transition: border-color .15s;
}
.cc-form-group input:focus,
.cc-form-group select:focus,
.cc-form-group textarea:focus {
    outline: none;
    border-color: var(--accent);
}
.cc-form-group textarea { resize: vertical; min-height: 72px; }

.btn-generar {
    width: 100%;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 10px;
    font-size: .88rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: opacity .15s;
}
.btn-generar:hover { opacity: .88; }
.btn-generar:disabled { opacity: .55; cursor: not-allowed; }

/* ── Back link ───────────────────────────────────────────────────────────── */
.cc-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .82rem;
    color: var(--text-muted);
    text-decoration: none;
    margin-bottom: 18px;
    transition: color .15s;
}
.cc-back:hover { color: var(--text-primary); }

/* ── Promocion card ──────────────────────────────────────────────────────── */
.cc-prom-card {
    background: var(--bg-hover);
    border-radius: 8px;
    padding: 14px 16px;
}

/* ── Fieldset separator ──────────────────────────────────────────────────── */
.cc-fieldset {
    border: none;
    padding: 0;
    margin: 0 0 20px;
}
.cc-fieldset legend {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--text-muted);
    width: 100%;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border-color);
    float: none;
}
.cc-fieldset legend i {
    font-size: .85rem;
    color: var(--accent);
}
</style>

<main id="main-content">
    <div class="container" style="max-width:1100px;">

        <a href="<?= base_url('/contratos') ?>" class="cc-back">
            <i class="bi bi-arrow-left"></i> Volver a contratos
        </a>

        <p class="page-title" style="margin-bottom:6px;">Generar contrato</p>
        <p id="pageTitleCot" class="fw-bold" style="font-size:.85rem;color:var(--text-muted);margin-bottom:22px;">Cargando cotización…</p>


        <div class="cc-grid">

            <!-- ── COLUMNA IZQUIERDA: Preview ─────────────────────── -->
            <div class="d-flex flex-column gap-4">

                <!-- Cotización + ítems -->
                <div class="cc-card">
                    <div class="cc-card-title fs-5">
                        <i class="bi bi-file-earmark-text"></i> Detalle de la cotización
                    </div>
                    
                    <div class="shadow p-3 mb-1 bg-body-tertiary rounded">

                        <div id="previewCotizacion">
                            <div class="placeholder-glow d-flex flex-column gap-2">
                                <span class="placeholder col-8 rounded"></span>
                                <span class="placeholder col-6 rounded"></span>
                                <span class="placeholder col-10 rounded"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promoción -->
                <div class="cc-card">
                    <div class="shadow p-3 mb-5 bg-body-tertiary rounded">
                        <div class="cc-card-title">
                            <i class="bi bi-mortarboard"></i> Promoción escolar vinculada
                        </div>
                        <div class="cc-prom-card">
                            <div id="previewPromocion">
                                <div class="placeholder-glow d-flex flex-column gap-2">
                                    <span class="placeholder col-7 rounded"></span>
                                    <span class="placeholder col-5 rounded"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ── COLUMNA DERECHA: Formulario ────────────────────── -->
            <div class="cc-card" style="position:sticky;top:80px;">
                <div class="cc-card-title fs-5">
                    <i class="bi bi-pencil-square"></i> Datos del contrato
                </div>
                <div class="shadow p-3 mb-5 bg-body-tertiary rounded">

                    <fieldset class="cc-fieldset">
                        <legend><i class="bi bi-cash-coin"></i> Pago del adelanto</legend>
                        <div class="w-100 mb-4 shadow border bg-body-tertiary rounded"></div>
    
                        <div class="cc-form-group">
                            <label for="contratoAdelanto">Monto (S/.) <span style="color:var(--red-text)">*</span></label>
                            <input type="number" id="contratoAdelanto" min="1" step="0.01" placeholder="0.00">
                        </div>
    
                        <div class="cc-form-group">
                            <label for="contratoFechaFirma">Fecha de pago <span style="color:var(--red-text)">*</span></label>
                            <input type="date" id="contratoFechaFirma">
                        </div>
    
                        <div class="cc-form-group" style="margin-bottom:0;">
                            <label for="contratoFormaPago">Forma de pago</label>
                            <select id="contratoFormaPago">
                                <option value="">— Cargando... —</option>
                            </select>
                        </div>
                    </fieldset>
    
                    <fieldset class="cc-fieldset">
                        <legend><i class="bi bi-journal-text"></i> Notas del contrato</legend>
                        <div class="w-100 mb-4 shadow border bg-body-tertiary rounded"></div>
    
                        <div class="cc-form-group">
                            <label for="contratoClausulas">Cláusulas adicionales</label>
                            <textarea id="contratoClausulas" placeholder="Condiciones o acuerdos especiales…"></textarea>
                        </div>
    
                        <div class="cc-form-group" style="margin-bottom:0;">
                            <label for="contratoObservaciones">Observaciones</label>
                            <textarea id="contratoObservaciones" placeholder="Notas internas del contrato…"></textarea>
                        </div>
                    </fieldset>
                </div>


                <button class="btn-generar" id="btnGenerar">
                    <i class="bi bi-file-earmark-check"></i>
                    <span>Generar contrato</span>
                </button>
            </div>

        </div>
    </div>
</main>

<script>const BASE_URL = "<?= base_url('') ?>";</script>
<script type="module" src="<?= base_url('js/modules/contratos/contratoCrear.js') ?>"></script>

<?= $footer ?>
