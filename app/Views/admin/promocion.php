<?= $header ?>

<link rel="stylesheet" href="<?= base_url('css/admin-promocion.css') ?>">

<main class="main-content" id="main-content" x-data="adminPanel()" x-init="init()">
<div class="container">

    <!-- BREADCRUMB + HEADER -->
    <div class="sesiones-header d-flex justify-content-between" >
        <button class="btn-back btn btn-outline-secondary btn-sm py-2" onclick="history.back()">
            <i class="bi bi-arrow-left"></i> Atrás
        </button>
        <!-- Selector de promoción (si hay más de una) -->
        <!-- <?php if (count($todasPromociones) > 1): ?>
        <div style="position:relative;">
            <select id="selectorPromocion"
                    onchange="if(this.value) window.location.href='<?= base_url('admin/formularios/promocion/') ?>'+this.value"
                    style="appearance:none;background:var(--bg-input);border:1px solid var(--border);
                           border-radius:9px;padding:.4rem 2rem .4rem .75rem;font-size:.8rem;
                           font-weight:600;color:var(--text-primary);cursor:pointer;min-width:200px;max-width:280px;">
                <?php foreach ($todasPromociones as $tp): ?>
                <option value="<?= $tp['id'] ?>" <?= $tp['id'] == $promocion['id'] ? 'selected' : '' ?>>
                    <?= esc($tp['nombre_colegio'] ?? '') ?> — <?= esc($tp['nombre']) ?>
                    <?= $tp['nivel'] ? '(' . esc($tp['nivel']) . ')' : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
            <i class="bi bi-chevron-down" style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);
               pointer-events:none;font-size:.75rem;color:var(--text-muted);"></i>
        </div>
        <?php else: ?>
        <div>
            <p class="section-label" style="margin:0;"><?= esc($promocion['nombre']) ?></p>
            <div style="font-size:.82rem;color:var(--text-muted);">
                <?= esc($promocion['nombre_colegio'] ?? '') ?>
                <?= $promocion['nivel'] ? ' · ' . esc($promocion['nivel']) : '' ?>
            </div>
        </div>
        <?php endif; ?> -->
 
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
            <?php if (!empty($sesionesLink)): ?>
            <a href="<?= esc($sesionesLink) ?>"
               style="display:inline-flex;align-items:center;gap:.35rem;background:var(--accent);color:#fff;
                      border:none;border-radius:8px;padding:.4rem .85rem;font-size:.8rem;font-weight:600;
                      text-decoration:none;cursor:pointer;">
                <i class="bi bi-camera"></i> Ver sesiones
            </a>
            <?php endif; ?>

            <?php if (!empty($promocion['id_promocion_escolar'])): ?>
            <!-- Campaña vinculada a contrato: se pueden agregar formularios -->
            <button id="btnNuevoFormulario"
                    type="button"
                    style="display:inline-flex;align-items:center;gap:.35rem;background:var(--accent);color:#fff;
                           border:none;border-radius:8px;padding:.4rem .85rem;font-size:.8rem;font-weight:600;
                           cursor:pointer;">
                <i class="bi bi-person-plus-fill"></i> Agregar alumno
            </button>
            <button id="btnAbrirImportar"
                    type="button"
                    style="display:inline-flex;align-items:center;gap:.35rem;background:var(--bg-input);
                           border:1px solid var(--border);border-radius:8px;padding:.4rem .85rem;
                           font-size:.8rem;font-weight:600;cursor:pointer;color:var(--text-primary);">
                <i class="bi bi-upload"></i> Importar alumnos
            </button>
            <?php else: ?>
            <!-- Sin contrato vinculado: botones deshabilitados con tooltip -->
            <span title="Vincula esta campaña a un contrato para poder agregar formularios"
                  style="display:inline-flex;align-items:center;gap:.35rem;background:var(--bg-input);
                         border:1px dashed var(--border);border-radius:8px;padding:.4rem .85rem;
                         font-size:.8rem;font-weight:600;color:var(--text-muted);cursor:not-allowed;opacity:.6;">
                <i class="bi bi-person-plus-fill"></i> Nuevo formulario
            </span>
            <?php endif; ?>

            <a href="<?= base_url('admin/formularios/exportar/' . $promocion['id']) ?>"
               style="display:inline-flex;align-items:center;gap:.35rem;background:var(--bg-input);
                      border:1px solid var(--border);border-radius:8px;padding:.4rem .85rem;
                      font-size:.8rem;font-weight:600;text-decoration:none;color:var(--text-primary);">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
        </div>
    </div>

    <?php if (empty($promocion['id_promocion_escolar'])): ?>
    <!-- AVISO: sin contrato vinculado -->
    <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:10px;
                padding:.85rem 1.1rem;margin-bottom:1rem;display:flex;align-items:flex-start;gap:.65rem;font-size:.82rem;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b;margin-top:.1rem;flex-shrink:0;"></i>
        <div>
            <strong style="color:#f59e0b;">Esta campaña no está vinculada a ningún contrato.</strong>
            Para poder agregar o importar formularios de alumnos, primero debes vincularla a una promoción del sistema
            desde la <a href="<?= base_url('admin/formularios') ?>" style="color:var(--accent);">lista de campañas</a>.
        </div>
    </div>
    <?php endif; ?>

    <!-- ENLACE COMPARTIDO -->
    <div class="cot-table-cdivard mb-3" style="padding:1rem 1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div>
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">
                    <i class="bi bi-link-45deg me-1" style="color:var(--accent);"></i>Enlace compartido para alumnos
                </div>
                <div style="font-size:.8rem;color:var(--text-secondary);">Comparte este link con todos los alumnos — cada uno completa su propio formulario.</div>
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <code style="background:var(--bg-input);border:1px solid var(--border);border-radius:7px;padding:.35rem .75rem;font-size:.75rem;color:var(--text-primary);max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                    <?= esc($linkCompartido) ?>
                </code>
                <button onclick="navigator.clipboard.writeText('<?= esc($linkCompartido) ?>').then(()=>this.textContent='¡Copiado!').catch(()=>{}); setTimeout(()=>this.innerHTML='<i class=\'bi bi-clipboard\'></i> Copiar',2000)"
                        style="background:var(--accent);color:#fff;border:none;border-radius:7px;padding:.38rem .85rem;font-size:.78rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.3rem;white-space:nowrap;">
                    <i class="bi bi-clipboard"></i> Copiar
                </button>
                <a href="<?= esc($linkCompartido) ?>" target="_blank"
                   style="color:var(--text-muted);font-size:.8rem;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;">
                    <i class="bi bi-box-arrow-up-right"></i> Abrir
                </a>
            </div>
        </div>
    </div>
 
    <!-- BARRA DE PROMOCIÓN -->
    <div class="asis-promo-bar mb-3">
        <i class="bi bi-mortarboard"></i>
        <div>
            <div class="asis-promo-nombre"><?= esc($promocion['nombre'] ?? 'Promoción') ?></div>
            <div class="asis-promo-meta">
                <?= esc($promocion['nombre_colegio'] ?? 'Colegio no asignado') ?>
                <?= !empty($promocion['nivel']) ? ' · ' . esc($promocion['nivel']) : '' ?>
            </div>
        </div>
        <span class="asis-promo-sep">·</span>
        <div class="asis-promo-meta">Alumnos: <?= (int) ($promocion['total_alumnos'] ?? 0) ?></div>
        <div class="asis-promo-meta">Completados: <?= (int) ($promocion['completados'] ?? 0) ?>
            <?php if (!empty($promocion['num_estudiantes_esperados'])): ?> · Esperados: <?= (int) $promocion['num_estudiantes_esperados'] ?><?php endif; ?>
        </div>
        <?php if (empty($promocion['id_promocion_escolar'])): ?>
        <span class="asis-promo-sep">·</span>
        <div class="asis-promo-meta" style="color:var(--red-text);">Sin contrato vinculado</div>
        <?php endif; ?>
    </div>
 
    <!-- BARRA DE PROGRESO -->    <?php
        $totalStat = ($promocion['num_estudiantes_esperados'] ?? null)
            ?? $promocion['total_alumnos'];
        $pct = $totalStat > 0
            ? round(($promocion['completados'] / $totalStat) * 100) : 0;
    ?>
    <div class="cot-table-card mb-3" style="padding:1rem 1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:baseline;font-size:.82rem;margin-bottom:.5rem;">
            <span style="color:var(--text-muted);">Progreso de formularios</span>
            <strong><?= $promocion['completados'] ?><span style="color:var(--text-muted);font-weight:400;">/<?= $totalStat ?></span></strong>
        </div>
        <div style="height:8px;background:var(--border);border-radius:4px;overflow:hidden;">
            <div style="height:100%;width:<?= $pct ?>%;background:var(--accent);border-radius:4px;transition:width .5s;"></div>
        </div>
    </div>

    <!-- BÚSQUEDA + TABLA -->
    <div class="search-box mb-2" style="max-width:340px;">
        <input type="text" x-model="busqueda" placeholder="Buscar por nombre...">
        <button class="search-btn"><i class="bi bi-search"></i></button>
    </div>

    <div class="cot-table-card">
        <div class="table-responsive">
        <table class="table table-sm" style="font-size:.84rem;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Enlace del formulario</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alumnos as $i => $a): ?>
                <tr x-show="filtra('<?= addslashes(esc($a['nombre'])) ?>')" x-cloak>
                    <td style="color:var(--text-muted);"><?= $i + 1 ?></td>
                    <td style="font-weight:500;"><?= esc($a['nombre']) ?></td>
                    <td>
                        <?php if ($a['completado']): ?>
                            <span class="badge-aprobada">✓ Completado</span>
                        <?php else: ?>
                            <span class="badge-pendiente">⏳ Pendiente</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="adm-link-text" title="<?= public_url('formulario/' . $a['token']) ?>">
                            <?= public_url('formulario/' . $a['token']) ?>
                        </span>
                    </td>
                    <td class="text-end" style="white-space:nowrap;">
                        <button onclick="navigator.clipboard.writeText('<?= public_url('formulario/' . $a['token']) ?>')"
                                style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:.8rem;padding:.2rem .4rem;"
                                title="Copiar enlace">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <?php if (!$a['completado']): ?>
                        <a href="<?= public_url('formulario/' . $a['token']) ?>" target="_blank"
                           style="color:var(--text-muted);font-size:.8rem;padding:.2rem .4rem;"
                           title="Abrir formulario">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

</div>

<form id="formImportarAlumnos" style="display:none;" method="post" action="<?= base_url('admin/formularios/alumno/importar') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="promocion_id" value="<?= (int) $promocion['id'] ?>">
</form>

</main>

<!-- MODAL IMPORTAR -->
<div id="importModalOverlay" class="adm-overlay" style="display:none;">    <div class="adm-modal">
        <h2><i class="bi bi-upload me-2" style="color:var(--accent);"></i>Importar alumnos</h2>
        <p>Escribe un nombre por línea. Se generará un enlace único para cada alumno.</p>
        <div id="importError" style="display:none;background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.3);border-radius:8px;padding:.75rem .95rem;font-size:.82rem;color:#dc3545;margin-bottom:1rem;"></div>
        <textarea id="txtNombresImportar"
                  placeholder="Juan García Pérez&#10;María López Torres&#10;Carlos Mendoza Lima"></textarea>
        <div class="adm-modal-footer">
            <button type="button" id="btnCancelarImportar" class="btn btn-secondary btn-sm">Cancelar</button>
            <button type="button" id="btnConfirmarImportar" class="btn btn-sm"
                    style="background:var(--accent);color:#fff;border:none;font-weight:600;padding:.4rem 1rem;border-radius:7px;">
                <span id="importBtnText"><i class="bi bi-check-circle me-1"></i>Importar</span>
            </button>
        </div>
    </div>
</div>

<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
const BASE_URL         = "<?= rtrim(base_url(''), '/') . '/' ?>";
const PROM_ID          = <?= (int) $promocion['id'] ?>;
const CSRF_TOKEN_NAME  = "<?= csrf_header() ?>";
const CSRF_HASH        = "<?= csrf_hash() ?>";
const CSRF_HEADER_NAME = "<?= csrf_header() ?>";

function adminPanel() {
    return {
        busqueda:      '',
        modalImportar: false,
        nombresTexto:  '',
        importando:    false,

        init() {},

        filtra(nombre) {
            if (!this.busqueda.trim()) return true;
            return nombre.toLowerCase().includes(this.busqueda.toLowerCase());
        },

        mostrarErrorImportacion(msg) {
            const el = document.getElementById('importError');
            if (!el) return;
            if (!msg) {
                el.style.display = 'none';
                el.textContent = '';
                return;
            }
            el.textContent = msg;
            el.style.display = 'block';
        },

        abrirImportar() {
            this.nombresTexto  = '';
            this.modalImportar = true;
            this.mostrarErrorImportacion('');
        },

        async importar() {
            const nombres = this.nombresTexto
                .split('\n').map(n => n.trim()).filter(n => n.length > 0);
            if (!nombres.length) {
                this.mostrarErrorImportacion('Escribe al menos un nombre.');
                return;
            }

            this.importando = true;
            this.mostrarErrorImportacion('');
            try {
                const r = await fetch(BASE_URL + 'admin/formularios/alumno/importar', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        [CSRF_TOKEN_NAME]: CSRF_HASH,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ promocion_id: PROM_ID, nombres }),
                });

                let data;
                try {
                    data = await r.json();
                } catch (jsonErr) {
                    throw new Error('Respuesta inválida del servidor.');
                }

                if (!r.ok) {
                    throw new Error(data.error || 'Error al importar.');
                }

                if (data.ok) {
                    this.modalImportar = false;
                    setTimeout(() => location.reload(), 400);
                } else {
                    this.mostrarErrorImportacion(data.error || 'Error al importar.');
                }
            } catch (err) {
                this.mostrarErrorImportacion(err.message || 'Error de conexión.');
            } finally {
                this.importando = false;
            }
        },
    };
}
</script>

<?php if (!empty($promocion['id_promocion_escolar'])): ?>
<?php
    $hayCuadros  = (int) ($promocion['cuadros_total']  ?? 0) > 0;
    $hayAnuarios = (int) ($promocion['anuarios_total'] ?? 0) > 0;
?>
<!-- ══════ MODAL NUEVO FORMULARIO (alumno individual — datos completos) ══════ -->
<div id="modalNuevoFormulario"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1060;
            align-items:flex-start;justify-content:center;padding:2rem 1rem;overflow-y:auto;">
    <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:14px;
                width:100%;max-width:520px;padding:1.5rem;box-shadow:0 8px 40px rgba(0,0,0,.35);">

        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <h6 style="margin:0;font-size:.95rem;font-weight:700;">
                <i class="bi bi-person-plus-fill me-2" style="color:var(--accent);"></i>Agregar alumno
            </h6>
            <button id="btnCerrarNuevoForm"
                    style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.1rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="nfErrorGlobal"
             style="display:none;background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.3);
                    border-radius:8px;padding:.65rem .9rem;font-size:.8rem;color:#dc3545;margin-bottom:1rem;"></div>

        <!-- ── Sección: Alumno ─────────────────────────────────────────────── -->
        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                    color:var(--text-muted);margin-bottom:.75rem;">Datos del alumno</div>

        <div style="margin-bottom:.85rem;">
            <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.3rem;">
                Nombres y apellidos *
            </label>
            <input type="text" id="nfNombre" class="form-control form-control-sm"
                   maxlength="150" placeholder="Nombres y apellidos">
        </div>
        <div style="margin-bottom:.85rem;">
            <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.3rem;">
                Fecha de nacimiento *
            </label>
            <input type="date" id="nfFechaNacimiento" class="form-control form-control-sm"
                   min="2005-01-01" max="<?= date('Y-m-d', strtotime('-5 years')) ?>">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.85rem;">
            <div>
                <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Color favorito</label>
                <input type="text" id="nfColor" class="form-control form-control-sm" placeholder="Ej: Azul">
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.3rem;">¿Qué quiere ser?</label>
                <input type="text" id="nfProfesion" class="form-control form-control-sm" placeholder="Ej: Médico">
            </div>
        </div>

        <!-- ── Sección: Apoderado ──────────────────────────────────────────── -->
        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                    color:var(--text-muted);margin-bottom:.75rem;margin-top:1.1rem;
                    border-top:1px solid var(--border);padding-top:1rem;">Datos del apoderado</div>

        <div style="margin-bottom:.85rem;">
            <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.3rem;">
                Nombre completo del apoderado *
            </label>
            <input type="text" id="nfTutor" class="form-control form-control-sm"
                   maxlength="150" placeholder="Nombres y apellidos">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.85rem;">
            <div>
                <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Relación</label>
                <select id="nfRelacion" class="form-select form-select-sm">
                    <option value="Padre">Padre</option>
                    <option value="Madre">Madre</option>
                    <option value="Tutor">Tutor Legal</option>
                </select>
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Teléfono *</label>
                <input type="tel" id="nfTelefono" class="form-control form-control-sm"
                       placeholder="987654321" maxlength="9" inputmode="numeric">
            </div>
        </div>
        <div style="margin-bottom:.85rem;">
            <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.3rem;">
                Correo electrónico
            </label>
            <input type="email" id="nfEmail" class="form-control form-control-sm"
                   placeholder="Opcional" maxlength="120">
        </div>

        <!-- ── Sección: Productos (condicional) ────────────────────────────── -->
        <?php if ($hayCuadros || $hayAnuarios): ?>
        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                    color:var(--text-muted);margin-bottom:.75rem;margin-top:1.1rem;
                    border-top:1px solid var(--border);padding-top:1rem;">Productos</div>
        <?php if ($hayCuadros): ?>
        <div style="margin-bottom:.85rem;">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.45rem;">
                <input type="checkbox" id="nfTieneCuadro"
                       style="accent-color:var(--accent);width:1rem;height:1rem;cursor:pointer;">
                <label for="nfTieneCuadro" style="font-size:.82rem;font-weight:600;cursor:pointer;">Cuadro escolar</label>
            </div>
            <select id="nfCuadroTamano" class="form-select form-select-sm" style="display:none;">
                <option value="">— Selecciona un tamaño —</option>
                <option value="20x30 cm">20×30 cm</option>
                <option value="30x40 cm">30×40 cm</option>
                <option value="40x50 cm">40×50 cm</option>
                <option value="50x60 cm">50×60 cm</option>
            </select>
        </div>
        <?php endif; ?>
        <?php if ($hayAnuarios): ?>
        <div style="margin-bottom:.85rem;">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.45rem;">
                <input type="checkbox" id="nfTieneAnuario"
                       style="accent-color:var(--accent);width:1rem;height:1rem;cursor:pointer;">
                <label for="nfTieneAnuario" style="font-size:.82rem;font-weight:600;cursor:pointer;">Anuario</label>
            </div>
            <select id="nfAnuarioModelo" class="form-select form-select-sm" style="display:none;">
                <option value="">— Selecciona un modelo —</option>
                <option value="Clásico Tapa Dura">Clásico Tapa Dura</option>
                <option value="Premium Cuero">Premium Cuero</option>
                <option value="Digital + Físico">Digital + Físico</option>
            </select>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- ── Sección: Autorizaciones ────────────────────────────────────── -->
        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                    color:var(--text-muted);margin-bottom:.75rem;margin-top:1.1rem;
                    border-top:1px solid var(--border);padding-top:1rem;">Autorizaciones</div>

        <div style="display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.65rem;">
            <input type="checkbox" id="nfAceptaImagenes"
                   style="margin-top:.1rem;accent-color:var(--accent);width:1rem;height:1rem;cursor:pointer;flex-shrink:0;">
            <label for="nfAceptaImagenes" style="font-size:.79rem;line-height:1.45;cursor:pointer;">
                Autoriza el uso de imágenes del alumno con fines fotográficos.
            </label>
        </div>
        <div style="display:flex;align-items:flex-start;gap:.5rem;margin-bottom:1.25rem;">
            <input type="checkbox" id="nfAceptaDatos"
                   style="margin-top:.1rem;accent-color:var(--accent);width:1rem;height:1rem;cursor:pointer;flex-shrink:0;">
            <label for="nfAceptaDatos" style="font-size:.79rem;line-height:1.45;cursor:pointer;">
                Acepta la política de tratamiento de datos personales.
            </label>
        </div>

        <!-- Footer -->
        <div style="display:flex;justify-content:flex-end;gap:.6rem;">
            <button id="btnCancelarNuevoForm" type="button"
                    style="background:var(--bg-input);color:var(--text-primary);border:1px solid var(--border);
                           border-radius:8px;padding:.4rem .9rem;font-size:.82rem;font-weight:600;cursor:pointer;">
                Cancelar
            </button>
            <button id="btnGuardarNuevoForm" type="button"
                    style="background:var(--accent);color:#fff;border:none;border-radius:8px;
                           padding:.4rem 1.1rem;font-size:.82rem;font-weight:600;cursor:pointer;">
                <i class="bi bi-check-circle me-1"></i> Guardar alumno
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const modal          = document.getElementById('modalNuevoFormulario');
    const btnAbrir       = document.getElementById('btnNuevoFormulario');
    const btnCerrar1     = document.getElementById('btnCerrarNuevoForm');
    const btnCerrar2     = document.getElementById('btnCancelarNuevoForm');
    const btnGuardar     = document.getElementById('btnGuardarNuevoForm');
    const errorEl        = document.getElementById('nfErrorGlobal');
    const importOverlay  = document.getElementById('importModalOverlay');
    const btnAbrirImport = document.getElementById('btnAbrirImportar');
    const btnCancelarImport = document.getElementById('btnCancelarImportar');
    const btnConfirmarImport = document.getElementById('btnConfirmarImportar');
    const txtNombresImportar = document.getElementById('txtNombresImportar');
    const importErrorEl = document.getElementById('importError');
    const importBtnText = document.getElementById('importBtnText');
    let savingAlumno = false;
    const g = id => document.getElementById(id);

    function resetForm() {
        ['nfNombre','nfFechaNacimiento','nfColor','nfProfesion',
         'nfTutor','nfTelefono','nfEmail'].forEach(id => { const el = g(id); if (el) el.value = ''; });
        const rel = g('nfRelacion'); if (rel) rel.value = 'Padre';
        ['nfTieneCuadro','nfTieneAnuario','nfAceptaImagenes','nfAceptaDatos'].forEach(id => {
            const el = g(id); if (el) el.checked = false;
        });
        const ct = g('nfCuadroTamano');  if (ct) { ct.value = ''; ct.style.display = 'none'; }
        const am = g('nfAnuarioModelo'); if (am) { am.value = ''; am.style.display = 'none'; }
        errorEl.style.display = 'none';
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar alumno';
    }

    function focusField(el) {
        if (!el) return;
        if (typeof el.scrollIntoView === 'function') {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        el.focus({ preventScroll: true });
    }

    function abrir() {
        resetForm();
        modal.style.display = 'flex';
        const n = g('nfNombre'); if (n) focusField(n);
    }

    function cerrar() { modal.style.display = 'none'; }

    window.abrirImportModal = function() {
        if (!importOverlay || !txtNombresImportar || !importBtnText || !importErrorEl) {
            alert('No se pudo abrir el modal de importación. Intenta recargar la página.');
            return;
        }
        importErrorEl.style.display = 'none';
        importErrorEl.textContent = '';
        txtNombresImportar.value = '';
        importBtnText.innerHTML = '<i class="bi bi-check-circle me-1"></i>Importar';
        importOverlay.style.display = 'flex';
        txtNombresImportar.focus();
    };

    window.cerrarImportModal = function() {
        if (!importOverlay) return;
        importOverlay.style.display = 'none';
    };

    window.confirmarImportar = async function() {
        if (!txtNombresImportar || !importErrorEl || !importBtnText || !btnConfirmarImport) return;

        const nombres = txtNombresImportar.value
            .split('\n').map(n => n.trim()).filter(n => n.length > 0);

        if (!nombres.length) {
            importErrorEl.textContent = 'Escribe al menos un nombre.';
            importErrorEl.style.display = 'block';
            return;
        }

        importBtnText.textContent = 'Importando...';
        importBtnText.style.opacity = '0.7';
        btnConfirmarImport.disabled = true;
        try {
            const form = document.getElementById('formImportarAlumnos');
            if (!form) {
                throw new Error('No se encontró el formulario de importación.');
            }

            const formData = new FormData(form);
            nombres.forEach(n => formData.append('nombres[]', n));

            const r = await fetch(BASE_URL + 'admin/formularios/alumno/importar', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData,
            });

            let data;
            try {
                data = await r.json();
            } catch (jsonErr) {
                const text = await r.text();
                throw new Error('Respuesta inválida del servidor: ' + (text || jsonErr.message));
            }

            if (!r.ok) {
                throw new Error(data.error || 'Error al importar.');
            }

            if (data.ok) {
                window.cerrarImportModal();
                setTimeout(() => location.reload(), 400);
            } else {
                throw new Error(data.error || 'Error al importar.');
            }
        } catch (err) {
            console.error('Import error:', err);
            importErrorEl.textContent = err.message || 'Error de conexión.';
            importErrorEl.style.display = 'block';
        } finally {
            importBtnText.innerHTML = '<i class="bi bi-check-circle me-1"></i>Importar';
            importBtnText.style.opacity = '1';
            btnConfirmarImport.disabled = false;
        }
    };

    const cuadroChk  = g('nfTieneCuadro');
    const anuarioChk = g('nfTieneAnuario');
    if (cuadroChk)  cuadroChk.addEventListener('change',  () => { const s = g('nfCuadroTamano');  if (s) s.style.display = cuadroChk.checked  ? '' : 'none'; });
    if (anuarioChk) anuarioChk.addEventListener('change', () => { const s = g('nfAnuarioModelo'); if (s) s.style.display = anuarioChk.checked ? '' : 'none'; });

    btnAbrir.addEventListener('click', abrir);
    btnCerrar1.addEventListener('click', cerrar);
    btnCerrar2.addEventListener('click', cerrar);
    modal.addEventListener('click', e => { if (e.target === modal) cerrar(); });
    if (btnAbrirImport && !btnAbrirImport.dataset.listenerAttached) {
        btnAbrirImport.addEventListener('click', abrirImportModal);
        btnAbrirImport.dataset.listenerAttached = '1';
    }
    if (btnCancelarImport && !btnCancelarImport.dataset.listenerAttached) {
        btnCancelarImport.addEventListener('click', cerrarImportModal);
        btnCancelarImport.dataset.listenerAttached = '1';
    }
    if (importOverlay && !importOverlay.dataset.listenerAttached) {
        importOverlay.addEventListener('click', e => { if (e.target === importOverlay) cerrarImportModal(); });
        importOverlay.dataset.listenerAttached = '1';
    }
    if (btnConfirmarImport && !btnConfirmarImport.dataset.listenerAttached) {
        btnConfirmarImport.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            confirmarImportar();
        });
        btnConfirmarImport.dataset.listenerAttached = '1';
    }

    if (btnGuardar && !btnGuardar.dataset.listenerAttached) {
        btnGuardar.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (savingAlumno) {
                return;
            }
            savingAlumno = true;
            errorEl.style.display = 'none';

            const RE_LETRAS = /^[A-Za-záéíóúüñÁÉÍÓÚÜÑ\s'.,-]+$/;
            const RE_TEL    = /^9\d{8}$/;
            const RE_EMAIL  = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

            const nombre    = (g('nfNombre')?.value          ?? '').trim();
            const fecha     =  g('nfFechaNacimiento')?.value ?? '';
            const color     = (g('nfColor')?.value           ?? '').trim();
            const profesion = (g('nfProfesion')?.value       ?? '').trim();
            const tutor     = (g('nfTutor')?.value           ?? '').trim();
            const tel       = (g('nfTelefono')?.value        ?? '').trim();
            const email     = (g('nfEmail')?.value           ?? '').trim();

            if (!nombre)                              { mostrarError('El nombre del alumno es obligatorio.');                                         focusField(g('nfNombre'));          savingAlumno = false; return; }
            if (!RE_LETRAS.test(nombre))              { mostrarError('El nombre solo puede contener letras y espacios (sin números ni emojis).');     focusField(g('nfNombre'));          savingAlumno = false; return; }
            if (!fecha)                               { mostrarError('La fecha de nacimiento es obligatoria.');                                       focusField(g('nfFechaNacimiento')); savingAlumno = false; return; }
            if (fecha < '2005-01-01')                 { mostrarError('La fecha de nacimiento no puede ser anterior al año 2005.');                    focusField(g('nfFechaNacimiento')); savingAlumno = false; return; }
            if (fecha > '<?= date('Y-m-d', strtotime('-5 years')) ?>')  { mostrarError('El alumno debe tener al menos 5 años de edad.');             focusField(g('nfFechaNacimiento')); savingAlumno = false; return; }
            if (color     && !RE_LETRAS.test(color))      { mostrarError('El color favorito solo puede contener letras y espacios.');                 focusField(g('nfColor'));           savingAlumno = false; return; }
            if (profesion && !RE_LETRAS.test(profesion))  { mostrarError('La profesión solo puede contener letras y espacios.');                     focusField(g('nfProfesion'));       savingAlumno = false; return; }
            if (!tutor)                               { mostrarError('El nombre del apoderado es obligatorio.');                                      focusField(g('nfTutor'));           savingAlumno = false; return; }
            if (!RE_LETRAS.test(tutor))               { mostrarError('El nombre del apoderado solo puede contener letras y espacios.');               focusField(g('nfTutor'));           savingAlumno = false; return; }
            if (!tel)                                 { mostrarError('El teléfono es obligatorio.');                                                  focusField(g('nfTelefono'));        savingAlumno = false; return; }
            if (!RE_TEL.test(tel))                    { mostrarError('El teléfono debe tener 9 dígitos y comenzar con 9 (ej: 987654321).');          focusField(g('nfTelefono'));        savingAlumno = false; return; }
            if (email && !RE_EMAIL.test(email))       { mostrarError('El correo no es válido (ej: nombre@ejemplo.com).');                            focusField(g('nfEmail'));           savingAlumno = false; return; }

            const tieneCuadro  = g('nfTieneCuadro')?.checked  ?? false;
            const tieneAnuario = g('nfTieneAnuario')?.checked ?? false;
            if (tieneCuadro  && !g('nfCuadroTamano')?.value)  { mostrarError('Selecciona el tamaño del cuadro.');  focusField(g('nfCuadroTamano'));  savingAlumno = false; return; }
            if (tieneAnuario && !g('nfAnuarioModelo')?.value)  { mostrarError('Selecciona el modelo del anuario.'); focusField(g('nfAnuarioModelo')); savingAlumno = false; return; }

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';

            try {
                const r = await fetch(BASE_URL + 'admin/formularios/alumno/agregar-completo', {
                    method:  'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        [CSRF_TOKEN_NAME]: CSRF_HASH,
                    },
                    body: JSON.stringify({
                        promocion_id:     PROM_ID,
                        nombre_alumno:    nombre,
                        fecha_nacimiento: fecha,
                        color_favorito:   color     || null,
                        profesion_futura: profesion || null,
                        nombre_tutor:     tutor,
                        relacion_tutor:   g('nfRelacion')?.value ?? 'Padre',
                        telefono:         tel,
                        email:            email     || null,
                        tiene_cuadro:     tieneCuadro,
                        cuadro_tamano:    g('nfCuadroTamano')?.value  || null,
                        tiene_anuario:    tieneAnuario,
                        anuario_modelo:   g('nfAnuarioModelo')?.value || null,
                        acepta_imagenes:  g('nfAceptaImagenes')?.checked ?? false,
                        acepta_datos:     g('nfAceptaDatos')?.checked    ?? false,
                    }),
                });
                let data;
                try {
                    data = await r.json();
                } catch (jsonErr) {
                    throw new Error('Respuesta inválida del servidor.');
                }
                if (!r.ok) {
                    throw new Error(data.error || 'No se pudo guardar el alumno.');
                }
                if (data.ok) {
                    cerrar();
                    location.reload();
                } else {
                    mostrarError(data.error || 'No se pudo guardar el alumno.');
                }
            } catch (err) {
                mostrarError(err.message || 'Error de conexión. Intenta de nuevo.');
            } finally {
                savingAlumno = false;
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar alumno';
            }
        });
        btnGuardar.dataset.listenerAttached = '1';
    }

    function mostrarError(msg) {
        errorEl.textContent   = msg;
        errorEl.style.display = '';
        errorEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
})();
</script>
<?php endif; ?>

<?= $footer ?>
