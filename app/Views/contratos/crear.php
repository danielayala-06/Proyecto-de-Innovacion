<?= $header ?>
<link rel="stylesheet" href="<?= base_url('css/contratos-crear.css') ?>">

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

                        <div class="cc-form-group">
                            <label for="contratoAdelanto">Monto (S/.) <span style="color:var(--red-text)">*</span></label>
                            <input type="number" id="contratoAdelanto" min="0.01" step="0.01" placeholder="0.00" onwheel="this.blur()"
                                   oninput="if(parseFloat(this.value)<0)this.value=''">
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
                            <input type="text" id="contratoFormaPagoOtro"
                                   placeholder="Ej: Tarjeta, Depósito BCP…"
                                   maxlength="60" style="display:none;margin-top:6px;">
                        </div>
                    </fieldset>

                    <fieldset class="cc-fieldset">
                        <legend><i class="bi bi-camera"></i> Sesiones fotográficas <span style="font-weight:400;font-size:.75rem;color:var(--text-muted);">(opcional)</span></legend>

                        <div id="sesionesContainer"></div>

                        <button type="button" id="btnAgregarSesion" class="cc-btn-add-sesion">
                            <i class="bi bi-plus-circle-fill"></i>Agregar sesión
                        </button>
                        <p style="font-size:.73rem;color:var(--text-muted);margin:.5rem 0 0;">
                            Máximo 3 sesiones &middot; horario: 07:00 – 22:00 &middot; hasta 18 meses desde hoy.
                        </p>
                    </fieldset>

                    <fieldset class="cc-fieldset" style="margin-bottom:0;">
                        <legend><i class="bi bi-chat-left-text"></i> Observaciones <span style="font-weight:400;font-size:.75rem;color:var(--text-muted);">(opcional)</span></legend>
                        <div class="cc-form-group" style="margin-bottom:0;">
                            <textarea id="contratoObservaciones" maxlength="400"
                                      placeholder="Indicaciones especiales, condiciones adicionales…"
                                      style="min-height:64px;"></textarea>
                            <p style="font-size:.71rem;color:var(--text-muted);margin:.3rem 0 0;text-align:right;">
                                <span id="obsCount">0</span>/400
                            </p>
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

<!-- ── MODAL DE CONFIRMACIÓN DE CONTRATO ───────────────────────────────────── -->
<div class="modal fade" id="modalConfirmarContrato" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:540px;">
        <div class="modal-content">

            <div class="modal-header" style="background:var(--accent,#B49040);border-bottom:none;padding:1rem 1.25rem;border-radius:14px 14px 0 0;">
                <div>
                    <h6 class="modal-title mb-0" style="color:#fff;font-size:1rem;font-weight:700;letter-spacing:.3px;">
                        <i class="bi bi-shield-check me-2"></i>Confirmar generación de contrato
                    </h6>
                    <p class="mb-0 mt-1" style="font-size:.75rem;color:rgba(255,255,255,.75);">
                        Revisa los montos antes de continuar. Esta acción no se puede deshacer.
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding:1.25rem;background:var(--bg-surface,#FAFAF8);">

                <!-- Cliente + cotización -->
                <div style="margin-bottom:1rem;padding:.75rem 1rem;background:var(--bg-hover);border-radius:8px;border:1px solid var(--border-color);">
                    <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.5rem;">Cotización</p>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div id="confClienteNombre" style="font-weight:600;font-size:.9rem;"></div>
                            <div id="confCotId" style="font-size:.75rem;color:var(--text-muted);"></div>
                        </div>
                        <div id="confTotalCot" style="font-size:1rem;font-weight:700;color:var(--text-primary);"></div>
                    </div>
                </div>

                <!-- Desglose de pago -->
                <div style="border:1px solid var(--border-color);border-radius:8px;overflow:hidden;margin-bottom:1rem;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border-color);">
                        <div style="padding:.75rem 1rem;border-right:1px solid var(--border-color);">
                            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">Adelanto recibido</p>
                            <p id="confAdelanto" style="font-size:1.35rem;font-weight:800;color:#198754;margin:0;"></p>
                        </div>
                        <div style="padding:.75rem 1rem;">
                            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">Saldo pendiente</p>
                            <p id="confSaldo" style="font-size:1.35rem;font-weight:800;color:#dc3545;margin:0;"></p>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;">
                        <div style="padding:.65rem 1rem;border-right:1px solid var(--border-color);">
                            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.2rem;">Fecha de pago</p>
                            <p id="confFecha" style="font-size:.85rem;font-weight:600;margin:0;"></p>
                        </div>
                        <div style="padding:.65rem 1rem;">
                            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.2rem;">Forma de pago</p>
                            <p id="confFormaPago" style="font-size:.85rem;font-weight:600;margin:0;"></p>
                        </div>
                    </div>
                </div>

                <!-- Porcentaje adelanto -->
                <div id="confPctWrap" style="margin-bottom:.75rem;">
                    <div style="display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:.3rem;">
                        <span style="color:var(--text-muted);">Porcentaje cubierto por el adelanto</span>
                        <span id="confPctLabel" style="font-weight:700;"></span>
                    </div>
                    <div style="height:6px;background:var(--border-color);border-radius:4px;overflow:hidden;">
                        <div id="confPctBar" style="height:100%;background:#198754;border-radius:4px;transition:width .3s;"></div>
                    </div>
                </div>

                <!-- Alerta si saldo = 0 o adelanto = total -->
                <div id="confAlerta" style="display:none;font-size:.78rem;padding:.5rem .75rem;border-radius:6px;"></div>
            </div>

            <div class="modal-footer" style="border-top:1px solid var(--border-color);padding:.9rem 1.25rem;gap:.5rem;background:var(--bg-surface,#FAFAF8);">
                <button type="button" class="btn btn-sm" data-bs-dismiss="modal"
                        style="color:var(--text-muted);background:none;border:1px solid var(--border);border-radius:7px;padding:.35rem .85rem;">
                    <i class="bi bi-arrow-left me-1"></i>Corregir
                </button>
                <button type="button" class="btn btn-sm" id="btnConfirmarContrato"
                        style="background:var(--accent,#B49040);color:#fff;font-weight:600;border:none;border-radius:7px;padding:.4rem 1.1rem;">
                    <i class="bi bi-file-earmark-check me-1"></i>Confirmar y generar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── MODAL DE ADELANTO BAJO ───────────────────────────────────────────────── -->
<div class="modal fade" id="modalAdelantoBajo" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">

            <div class="modal-header" style="background:#b45309;border-bottom:none;padding:1rem 1.25rem;">
                <div>
                    <h6 class="modal-title mb-0" style="color:#fff;font-size:.95rem;font-weight:700;letter-spacing:.3px;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Adelanto inusualmente bajo
                    </h6>
                </div>
            </div>

            <div class="modal-body" style="padding:1.35rem 1.25rem;background:var(--bg-surface,#FAFAF8);">
                <p id="adelantoBajoMensaje" style="font-size:.88rem;color:var(--text-primary);line-height:1.6;margin:0;"></p>
                <p style="font-size:.8rem;color:var(--text-muted);margin-top:.75rem;margin-bottom:0;">
                    Si ya coordinó este monto con el cliente, puede continuar. De lo contrario, le recomendamos regresar y ajustar el monto.
                </p>
            </div>

            <div class="modal-footer" style="border-top:1px solid var(--border-color);padding:.85rem 1.25rem;gap:.5rem;background:var(--bg-surface,#FAFAF8);">
                <button type="button" class="btn btn-sm" id="btnAdelantoBajoVolver"
                        style="color:var(--text-muted);background:none;border:1px solid var(--border-color);border-radius:7px;padding:.35rem .85rem;">
                    <i class="bi bi-arrow-left me-1"></i>Volver y corregir
                </button>
                <button type="button" class="btn btn-sm" id="btnAdelantoBajoConfirmar"
                        style="background:#b45309;color:#fff;font-weight:600;border:none;border-radius:7px;padding:.4rem 1.1rem;">
                    <i class="bi bi-check2 me-1"></i>Sí, deseo continuar
                </button>
            </div>
        </div>
    </div>
</div>

<script>const BASE_URL = "<?= base_url('') ?>";</script>
<script type="module" src="<?= base_url('js/modules/contratos/contratoCrear.js') . '?v=' . filemtime(FCPATH . 'js/modules/contratos/contratoCrear.js') ?>"></script>

<?= $footer ?>
