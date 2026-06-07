<?= $header ?>

<link rel="stylesheet" href="<?= base_url('css/admin-promocion.css') ?>">

<main class="main-content" id="main-content" x-data="adminPanel()" x-init="init()">
<div class="container">

    <!-- BREADCRUMB + HEADER -->
    <div class="sesiones-header">
        <a href="<?= base_url('admin/formularios') ?>" class="btn-back">
            <i class="bi bi-arrow-left"></i> Formularios
        </a>
        <div style="flex:1;">
            <p class="section-label"><?= esc($promocion['nombre']) ?></p>
            <div style="font-size:.82rem;color:var(--text-muted);">
                <?= esc($promocion['nombre_colegio'] ?? '') ?>
                <?= $promocion['nivel'] ? ' · ' . esc($promocion['nivel']) : '' ?>
            </div>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <?php if (!empty($sesionesLink)): ?>
            <a href="<?= esc($sesionesLink) ?>"
               style="display:inline-flex;align-items:center;gap:.35rem;background:var(--accent);color:#fff;
                      border:none;border-radius:8px;padding:.4rem .85rem;font-size:.8rem;font-weight:600;
                      text-decoration:none;cursor:pointer;">
                <i class="bi bi-camera"></i> Ver sesiones
            </a>
            <?php endif; ?>
            <a href="<?= base_url('admin/formularios/exportar/' . $promocion['id']) ?>"
               style="display:inline-flex;align-items:center;gap:.35rem;background:var(--bg-input);
                      border:1px solid var(--border);border-radius:8px;padding:.4rem .85rem;
                      font-size:.8rem;font-weight:600;text-decoration:none;color:var(--text-primary);">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
            <button @click="abrirImportar()"
                    style="display:inline-flex;align-items:center;gap:.35rem;background:var(--bg-input);
                           border:1px solid var(--border);border-radius:8px;padding:.4rem .85rem;
                           font-size:.8rem;font-weight:600;cursor:pointer;color:var(--text-primary);">
                <i class="bi bi-upload"></i> Importar alumnos
            </button>
        </div>
    </div>

    <!-- ENLACE COMPARTIDO -->
    <div class="cot-table-card mb-3" style="padding:1rem 1.25rem;">
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

    <!-- ESTADÍSTICAS -->
    <div class="adm-stats">
        <div class="adm-stat">
            <div class="adm-stat-num"><?= $promocion['total_alumnos'] ?></div>
            <div class="adm-stat-label">Total alumnos</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-num green"><?= $promocion['completados'] ?></div>
            <div class="adm-stat-label">Completados</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-num amber"><?= $promocion['pendientes'] ?></div>
            <div class="adm-stat-label">Pendientes</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-num gold"><?= $stock['cuadros'] ?></div>
            <div class="adm-stat-label">Cuadros libres</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-num gold"><?= $stock['anuarios'] ?></div>
            <div class="adm-stat-label">Anuarios libres</div>
        </div>
    </div>

    <!-- BARRA DE PROGRESO -->
    <?php $pct = $promocion['total_alumnos'] > 0
        ? round(($promocion['completados'] / $promocion['total_alumnos']) * 100) : 0; ?>
    <div class="cot-table-card mb-3" style="padding:1rem 1.25rem;">
        <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.5rem;">
            <span style="color:var(--text-muted);">Progreso de formularios</span>
            <strong><?= $pct ?>%</strong>
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
                        <span class="adm-link-text" title="<?= base_url('formulario/' . $a['token']) ?>">
                            <?= base_url('formulario/' . $a['token']) ?>
                        </span>
                    </td>
                    <td class="text-end" style="white-space:nowrap;">
                        <button onclick="navigator.clipboard.writeText('<?= base_url('formulario/' . $a['token']) ?>')"
                                style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:.8rem;padding:.2rem .4rem;"
                                title="Copiar enlace">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <?php if (!$a['completado']): ?>
                        <a href="<?= base_url('formulario/' . $a['token']) ?>" target="_blank"
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
</main>

<!-- MODAL IMPORTAR -->
<div class="adm-overlay" x-show="modalImportar" x-cloak @click.self="modalImportar = false">
    <div class="adm-modal" @click.stop>
        <h2><i class="bi bi-upload me-2" style="color:var(--accent);"></i>Importar alumnos</h2>
        <p>Escribe un nombre por línea. Se generará un enlace único para cada alumno.</p>
        <textarea x-model="nombresTexto"
                  placeholder="Juan García Pérez&#10;María López Torres&#10;Carlos Mendoza Lima"></textarea>
        <div class="adm-modal-footer">
            <button class="btn btn-secondary btn-sm" @click="modalImportar = false">Cancelar</button>
            <button class="btn btn-sm" @click="importar()" :disabled="importando"
                    style="background:var(--accent);color:#fff;border:none;font-weight:600;padding:.4rem 1rem;border-radius:7px;">
                <span x-show="!importando"><i class="bi bi-check-circle me-1"></i>Importar</span>
                <span x-show="importando">Importando...</span>
            </button>
        </div>
    </div>
</div>

<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
const BASE_URL = "<?= base_url('') ?>";
const PROM_ID  = <?= (int) $promocion['id'] ?>;

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

        abrirImportar() {
            this.nombresTexto  = '';
            this.modalImportar = true;
        },

        async importar() {
            const nombres = this.nombresTexto
                .split('\n').map(n => n.trim()).filter(n => n.length > 0);
            if (!nombres.length) { alert('Escribe al menos un nombre.'); return; }

            this.importando = true;
            try {
                const r = await fetch(BASE_URL + 'admin/formularios/alumno/importar', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ promocion_id: PROM_ID, nombres }),
                });
                const data = await r.json();
                if (data.ok) {
                    this.modalImportar = false;
                    setTimeout(() => location.reload(), 400);
                } else {
                    alert(data.error || 'Error al importar.');
                }
            } catch { alert('Error de conexión.'); }
            finally  { this.importando = false; }
        },
    };
}
</script>

<?= $footer ?>
