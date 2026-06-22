<?= $header ?>
<main id="main-content">
    <div class="container">

        <!-- BREADCRUMB + HEADER -->
        <div class="sesiones-header">
            <a href="<?= base_url('/contratos') ?>" class="btn-back">
                <i class="bi bi-arrow-left"></i> Contratos
            </a>
            <div class="flex-fill">
                <p class="section-label">Gestión de Sesiones</p>
                <div class="sesiones-contrato-info">
                    <span><i class="bi bi-file-earmark-text"></i> Contrato #<?= str_pad($contrato['id_contrato'], 4, '0', STR_PAD_LEFT) ?></span>
                    <span class="text-muted">·</span>
                    <span><?= esc($contrato['cliente']) ?></span>
                    <span class="text-muted">·</span>
                    <span class="<?= $contrato['estado'] === 'ACTIVO' ? 'badge-aprobada' : 'badge-rechazada' ?>">
                        <?= esc($contrato['estado']) ?>
                    </span>
                </div>
            </div>
            <div class="d-flex justify-content-end w-25">
                <button type="button" class="align-self-end btn btn-nuevo-paquete" style="cursor:pointer;">Ir a formulario <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <?php if (empty($promociones)): ?>
        <div class="empty-state" style="margin-top:3rem;">
            <i class="bi bi-mortarboard" style="font-size:2.5rem;"></i>
            Este contrato no tiene promociones vinculadas.
        </div>
        <?php else: ?>

        <!-- TABS DE PROMOCIONES (ocultos, usados solo para navegar cuando hay varias) -->
        <div id="promocionesTabs" style="display:none;"></div>

        <!-- CONTENIDO PRINCIPAL -->
        <div id="sesionesContainer"></div>

        <?php endif; ?>
    </div>
</main>

<style>
/* ── Mini calendario (modal sesión) ─────────────────────────────────────── */
.sf-layout{display:flex;min-height:340px;}
.sf-cal{width:270px;min-width:250px;flex-shrink:0;padding:1rem;border-right:1px solid var(--border);background:var(--bg-page);}
.sf-form{flex:1;padding:1.25rem;min-width:0;}
.sf-cal-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;}
.sf-cal-lbl{font-size:.82rem;font-weight:700;color:var(--text-primary);}
.sf-cal-nav{background:none;border:1px solid var(--border);border-radius:6px;cursor:pointer;
            color:var(--text-secondary);padding:.18rem .45rem;line-height:1;font-size:.8rem;}
.sf-cal-nav:hover{background:var(--bg-hover);}
.sf-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;}
.mca-name{text-align:center;font-size:.65rem;font-weight:700;color:var(--text-muted);padding:.2rem 0;}
.mca-cell{display:flex;flex-direction:column;align-items:center;border-radius:5px;padding:.12rem 0 .22rem;cursor:pointer;transition:background .12s;}
.mca-cell.empty{cursor:default;}
.mca-cell.mca-past{opacity:.32;cursor:not-allowed;pointer-events:none;}
.mca-cell:not(.mca-past):not(.empty):hover{background:var(--bg-hover);}
.mca-cell.mca-hoy .mca-num{background:var(--accent);color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;}
.mca-cell.mca-sel{background:var(--bg-hover);outline:2px solid var(--accent);outline-offset:-2px;}
.mca-num{font-size:.74rem;font-weight:500;line-height:1.3;}
.mca-dots{display:flex;gap:2px;justify-content:center;min-height:5px;}
.mca-dot{width:5px;height:5px;border-radius:50%;display:inline-block;}
.mca-dot.ses-pendiente{background:#B8963E;}
.mca-dot.ses-finalizado{background:#22863a;}
.mca-dot.ses-cancelado{background:#dc3545;}
.sf-cal-legend{margin-top:.6rem;font-size:.68rem;color:var(--text-muted);display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;}
@media(max-width:600px){.sf-layout{flex-direction:column;}.sf-cal{width:100%;border-right:none;border-bottom:1px solid var(--border);}}
.mca-day-info{margin-top:.75rem;padding-top:.65rem;border-top:1px solid var(--border);max-height:160px;overflow-y:auto;}
.mca-day-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.4rem;}
.mca-ses-row{display:flex;align-items:flex-start;gap:.45rem;padding:.25rem 0;border-bottom:1px solid var(--border);}
.mca-ses-row:last-child{border-bottom:none;}
.mca-ses-hora{font-size:.75rem;font-weight:700;color:var(--accent);min-width:36px;flex-shrink:0;padding-top:1px;}
.mca-ses-info{flex:1;min-width:0;}
.mca-ses-name{font-size:.74rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mca-ses-meta{font-size:.67rem;color:var(--text-muted);}
.mca-day-empty{font-size:.73rem;color:var(--text-muted);text-align:center;padding:.4rem 0;}

/* ── Tabs de promociones (horizontal) ───────────────────────────────────── */
.promo-tabs-bar{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.1rem;}
.promo-tabs-bar .promo-tab{display:flex;flex-direction:row;align-items:center;gap:.5rem;
    padding:.5rem 1rem;border:1px solid var(--border);border-radius:8px;
    background:var(--bg-elevated);cursor:pointer;transition:all .15s;
    font-family:inherit;text-align:left;}
.promo-tabs-bar .promo-tab span{font-size:.82rem;font-weight:600;color:var(--text-secondary);}
.promo-tabs-bar .promo-tab small{font-size:.72rem;color:var(--text-muted);}
.promo-tabs-bar .promo-tab i{color:var(--text-muted);font-size:.9rem;}
.promo-tabs-bar .promo-tab:hover{background:var(--bg-hover);border-color:var(--accent);}
.promo-tabs-bar .promo-tab.active{background:var(--accent);border-color:var(--accent);}
.promo-tabs-bar .promo-tab.active span,.promo-tabs-bar .promo-tab.active small,
.promo-tabs-bar .promo-tab.active i{color:#fff;}

/* ── Tarjetas de sesión en cabecera de tabla ─────────────────────────────── */
.asis-th-slot{vertical-align:top;padding:0 !important;min-width:160px;border-right:1px solid var(--border);}
.asis-th-slot:last-of-type{border-right:none;}
.asis-slot-card{padding:.75rem;background:var(--bg-surface);}
.asis-slot-card.empty{display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:.45rem;text-align:center;min-height:80px;border-style:dashed;border-width:0;}
.asis-slot-card.filled{min-height:80px;}
.asis-th-clickable{cursor:pointer;}
.asis-th-clickable:hover .asis-slot-card.filled{background:var(--bg-hover);}
.asis-slot-lbl{font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
    color:var(--text-muted);display:block;}
.asis-slot-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;}
.asis-slot-actions{display:flex;gap:3px;}
.asis-slot-fecha{font-size:.77rem;color:var(--text-secondary);margin:.3rem 0;display:flex;align-items:center;gap:.3rem;flex-wrap:wrap;}
.asis-slot-empty-hint{font-size:.72rem;color:var(--text-muted);}
.asis-slot-obs{font-size:.7rem;color:var(--text-muted);margin-top:.3rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.asis-slot-add-btn{display:flex;align-items:center;justify-content:center;
    width:40px;height:40px;border:2px dashed var(--border);border-radius:8px;
    background:none;color:var(--text-muted);font-size:1.1rem;cursor:pointer;
    font-family:inherit;transition:border-color .15s,color .15s,background .15s;}
.asis-slot-add-btn:hover{border-color:var(--accent);color:var(--accent);background:color-mix(in srgb,var(--accent) 6%,transparent);}

/* ── Tabla de asistencia ─────────────────────────────────────────────────── */
.asis-section{border:1px solid var(--border);border-radius:12px;background:var(--bg-elevated);overflow:hidden;}
.asis-promo-bar{display:flex;align-items:center;gap:.65rem;padding:.9rem 1.2rem;
    background:color-mix(in srgb,var(--accent) 8%,var(--bg-elevated));
    border-bottom:2px solid var(--accent);}
.asis-promo-bar>i{font-size:1.15rem;color:var(--accent);flex-shrink:0;}
.asis-promo-nombre{font-weight:700;font-size:.9rem;color:var(--accent);}
.asis-promo-sep{color:var(--text-muted);font-size:.8rem;}
.asis-promo-meta{font-size:.78rem;color:var(--text-secondary);}
.asis-hdr{display:flex;align-items:center;justify-content:space-between;padding:.8rem 1.2rem .65rem;border-bottom:1px solid var(--border);}
.asis-hdr-title{font-weight:700;font-size:.9rem;display:flex;align-items:center;gap:.4rem;color:var(--text-primary);}
.asis-hdr-meta{font-size:.73rem;color:var(--text-muted);background:var(--bg-surface);border:1px solid var(--border);border-radius:6px;padding:3px 10px;}
.asis-hint{font-size:.71rem;color:var(--text-muted);padding:.35rem 1.2rem;margin:0;}
.asis-table-wrap{overflow-x:auto;}
.asis-table{width:100%;border-collapse:collapse;}
.asis-table th{padding:.45rem .6rem;font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);border-bottom:1px solid var(--border);text-align:center;background:var(--bg-surface);}
.asis-th-num{width:36px;}
.asis-th-nombre{text-align:left;min-width:160px;}
.asis-th-sesion{width:76px;}
.asis-th-sesion.disabled{opacity:.35;}
.asis-th-obs{min-width:100px;}
.asis-table td{padding:.3rem .5rem;border-bottom:1px solid var(--border);text-align:center;vertical-align:middle;}
.asis-table tr:last-child td{border-bottom:none;}
.asis-table tbody tr:hover td{background:var(--bg-hover);}
.asis-td-num{font-size:.71rem;color:var(--text-muted);font-weight:600;}
.asis-td-nombre{text-align:left;font-size:.83rem;}
.asis-td{cursor:pointer;user-select:none;padding:.25rem .35rem;}
.asis-td-click{cursor:pointer;}
.asis-table tbody tr:hover .asis-td-click{text-decoration:underline;text-decoration-color:var(--accent);text-underline-offset:2px;}
.asis-td.disabled{cursor:default;background:var(--bg-surface);opacity:.4;}
.asis-td-empty{text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.83rem;}
.asis-td-obs{padding:.25rem .5rem;}
.asis-cell{width:44px;height:44px;border:2px solid var(--border);border-radius:4px;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;transition:all .12s;margin:auto;}
.asis-td:hover .asis-cell.vacio{background:var(--bg-hover);border-color:var(--accent);}
.asis-cell.asistio{background:var(--accent);border-color:var(--accent);color:#fff;font-size:1.2rem;}
.asis-cell.falto{background:var(--bg-hover);border-color:var(--red-border,#dc3545);color:var(--red-text,#dc3545);font-size:1.2rem;}
.asis-obs-input{width:100%;border:1px solid var(--border);border-radius:5px;padding:2px 7px;font-size:.72rem;background:var(--bg-input);color:var(--text-primary);font-family:inherit;}
.asis-footer{display:flex;align-items:center;gap:1.5rem;padding:.7rem 1.2rem;border-top:1px solid var(--border);background:var(--bg-surface);flex-wrap:wrap;}
.asis-stat{display:flex;align-items:center;gap:.45rem;font-size:.78rem;color:var(--text-secondary);}
.asis-stat-val{font-size:.95rem;font-weight:700;color:var(--text-primary);}
.asis-stat-total{margin-left:auto;display:flex;align-items:center;gap:.6rem;font-size:.78rem;background:var(--bg-elevated);border:1px solid var(--border);border-radius:8px;padding:.35rem .85rem;}
.asis-stat-total .asis-stat-val{font-size:1.05rem;}
.asis-empty-hint{padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.83rem;}
</style>

<!-- ═══════ MODAL NUEVA / EDITAR SESIÓN ════════════════════════════════════════ -->
<div class="modal fade" id="modalSesion" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalSesionTitulo">Nueva sesión</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="sf-layout">

                    <!-- ── Mini calendario ─────────────────────────────── -->
                    <div class="sf-cal">
                        <div class="sf-cal-hdr">
                            <button class="sf-cal-nav" onclick="miniCalPrev()"><i class="bi bi-chevron-left"></i></button>
                            <span id="miniCalLabel" class="sf-cal-lbl"></span>
                            <button class="sf-cal-nav" onclick="miniCalNext()"><i class="bi bi-chevron-right"></i></button>
                        </div>
                        <div id="miniCalGrid" class="sf-cal-grid"></div>
                        <div class="sf-cal-legend">
                            <span class="mca-dot ses-pendiente"></span> Pendiente &nbsp;
                            <span class="mca-dot ses-finalizado"></span> Finalizado
                        </div>
                        <div id="miniCalDayInfo" class="mca-day-info" style="display:none;"></div>
                    </div>

                    <!-- ── Formulario ──────────────────────────────────── -->
                    <div class="sf-form">
                        <input type="hidden" id="sfId">
                        <input type="hidden" id="sfPromocion">

                        <div class="mb-3">
                            <label>Tipo de sesión</label>
                            <select class="form-select form-select-sm" id="sfTipo">
                                <option value="">Seleccionar...</option>
                                <option value="colegio">Colegio</option>
                                <option value="exteriores">Exteriores</option>
                                <option value="estudio">Estudio</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Fecha <small class="text-muted">(elige en el calendario)</small></label>
                            <input type="date" class="form-control form-control-sm" id="sfFecha">
                        </div>
                        <div class="mb-3">
                            <label>Hora</label>
                            <input type="time" class="form-control form-control-sm" id="sfHora" value="09:00" min="09:00" max="20:00">
                        </div>
                        <div class="mb-3">
                            <label>Observaciones</label>
                            <textarea class="form-control form-control-sm" id="sfObservaciones" rows="3"
                                placeholder="Detalles del lugar, indicaciones, etc."></textarea>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="guardarSesion()">Guardar sesión</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════ MODAL NUEVO ESTUDIANTE ════════════════════════════════════════════ -->
<div class="modal fade" id="modalEstudiante" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Registrar estudiante</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Datos del estudiante -->
                <p class="form-section-label"><i class="bi bi-person-badge"></i> Datos del estudiante</p>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label>Nombre completo *</label>
                        <input type="text" class="form-control" id="efNombreCompleto" maxlength="100" placeholder="Ej: Tony Quispe Mamani">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Fecha de nacimiento</label>
                        <input type="date" class="form-control" id="efNacimiento">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Color favorito</label>
                        <input type="text" class="form-control" id="efColor" maxlength="30">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Profesión futura</label>
                        <input type="text" class="form-control" id="efProfesion" maxlength="40">
                    </div>
                </div>

                <!-- Datos del apoderado -->
                <p class="form-section-label"><i class="bi bi-person-fill"></i> Datos del apoderado</p>
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <label>Nombre completo *</label>
                        <input type="text" class="form-control" id="apNombreCompleto" maxlength="100" placeholder="Ej: Mirian Tasayco Murillo">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Teléfono * (9 dígitos)</label>
                        <input type="tel" class="form-control" id="apTelefono" maxlength="9" pattern="\d{9}"
                               oninput="this.value=this.value.replace(/\D/g,'')">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Relación con el estudiante *</label>
                        <select class="form-select" id="apRelacion">
                            <option value="">--</option>
                            <option value="madre">Madre</option>
                            <option value="padre">Padre</option>
                            <option value="hermano">Hermano/a</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Correo</label>
                        <input type="email" class="form-control" id="apCorreo" maxlength="150">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="guardarEstudiante()">Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════ MODAL DETALLE SESIÓN ═══════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetalleSesion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="detalleSesionTitulo">
                    <i class="bi bi-calendar3-event me-2" style="color:var(--accent);"></i>
                    Detalle de sesión
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleSesionBody">
                <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                    <i class="bi bi-arrow-repeat"></i> Cargando...
                </div>
            </div>
            <div class="modal-footer" id="detalleSesionFooter">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════ MODAL PERFIL ESTUDIANTE ══════════════════════════════════════════ -->
<div class="modal fade" id="modalPerfilEstudiante" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-person-badge me-2" style="color:var(--accent);"></i>
                    <span id="perfilNombre">Perfil del estudiante</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="perfilBody">
                <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                    <i class="bi bi-arrow-repeat"></i> Cargando...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Datos para el JS -->
<script>
    const BASE_URL      = "<?= base_url('') ?>";
    const ID_CONTRATO   = <?= (int) $contrato['id_contrato'] ?>;
    const PROMOCIONES   = <?= json_encode(array_map(fn($p) => [
        'id_promocion' => (int) $p['id_promocion'],
        'nombre'       => $p['nombre'],
        'grado'        => $p['grado'],
        'seccion'      => $p['seccion'],
        'nombre_colegio' => $p['nombre_colegio'],
    ], $promociones)) ?>;
    const CONFIG_SESIONES = <?= json_encode(array_map(fn($c) => [
        'tipo_sesion'       => $c['tipo_sesion'],
        'num_sesiones'      => (int) $c['num_sesiones'],
        'lugar_descripcion' => $c['lugar_descripcion'],
        'nombre_paquete'    => $c['nombre_paquete'],
    ], $sesionesConfig)) ?>;
</script>
<script type="module" src="<?= base_url('js/modules/sesiones/sesionesMain.js') . '?v=' . filemtime(FCPATH . 'js/modules/sesiones/sesionesMain.js') ?>"></script>

<?= $footer ?>
