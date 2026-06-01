<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($promocion['nombre']) ?> — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#F7F4EE;color:#1A1714;font-size:15px}
[x-cloak]{display:none!important}

.topbar{background:#1A1714;padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.topbar-left{display:flex;align-items:center;gap:.6rem}
.topbar-dot{width:9px;height:9px;border-radius:50%;background:#B8963E;flex-shrink:0}
.topbar h1{font-family:'Playfair Display',serif;font-size:1.05rem;color:#F7F4EE;font-weight:700}
.topbar p{font-size:.7rem;color:#B8963E;letter-spacing:.1em;text-transform:uppercase;margin-top:1px}
.topbar-actions{display:flex;gap:.6rem;flex-wrap:wrap}

.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:opacity .15s,transform .1s}
.btn:hover{opacity:.88;transform:translateY(-1px)}
.btn-gold{background:#B8963E;color:#fff}
.btn-dark{background:#2E2B26;color:#fff}
.btn-light{background:#F7F4EE;color:#1A1714;border:1.5px solid #D6D0C8}
.btn-sm{padding:.35rem .7rem;font-size:.75rem;border-radius:7px}

.container{max-width:960px;margin:0 auto;padding:2rem 1.5rem}

/* ── TARJETAS ESTADÍSTICAS ── */
.stats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:.9rem;margin-bottom:1.75rem}
@media(max-width:700px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
.stat-card{background:#fff;border-radius:14px;padding:1.2rem 1rem;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center}
.stat-num{font-family:'Playfair Display',serif;font-size:1.9rem;font-weight:700;color:#1A1714;line-height:1}
.stat-num.gold{color:#B8963E}
.stat-label{font-size:.72rem;color:#6B6460;margin-top:.35rem;text-transform:uppercase;letter-spacing:.05em}

/* ── BARRA DE PROGRESO ── */
.progress-section{background:#fff;border-radius:14px;padding:1.2rem 1.4rem;margin-bottom:1.75rem;box-shadow:0 2px 10px rgba(0,0,0,.06)}
.prog-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:.65rem;font-size:.82rem}
.prog-bar{height:10px;background:#EDE8DC;border-radius:5px;overflow:hidden}
.prog-fill{height:100%;background:linear-gradient(90deg,#B8963E,#D4A84B);border-radius:5px;transition:width .5s}

/* ── BÚSQUEDA ── */
.search-bar{position:relative;margin-bottom:1rem}
.search-bar input{width:100%;background:#fff;border:1.5px solid #D6D0C8;border-radius:10px;padding:.6rem 1rem .6rem 2.5rem;font-family:'DM Sans',sans-serif;font-size:.88rem;color:#1A1714}
.search-bar input:focus{outline:none;border-color:#B8963E}
.search-bar::before{content:'🔍';position:absolute;left:.75rem;top:50%;transform:translateY(-50%);font-size:.85rem}

/* ── TABLA ── */
.card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden}
table{width:100%;border-collapse:collapse}
thead tr{background:#F7F4EE}
th{padding:.75rem 1rem;text-align:left;font-size:.7rem;font-weight:600;color:#6B6460;text-transform:uppercase;letter-spacing:.06em}
td{padding:.8rem 1rem;border-top:1px solid #F7F4EE;font-size:.87rem;vertical-align:middle}
tr:hover td{background:#FDFCF9}
.badge{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:6px;font-size:.7rem;font-weight:600;letter-spacing:.04em}
.badge-green{background:#E6F5EC;color:#1A5E2E}
.badge-amber{background:#F8F0D8;color:#7A5000}
.link-text{font-size:.72rem;color:#6B6460;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block}

/* ── MODAL ── */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem}
.modal{background:#fff;border-radius:20px;padding:2rem 1.75rem;width:100%;max-width:480px;box-shadow:0 16px 48px rgba(0,0,0,.2)}
.modal h2{font-family:'Playfair Display',serif;font-size:1.3rem;margin-bottom:.5rem}
.modal p{font-size:.82rem;color:#6B6460;margin-bottom:1.25rem}
.modal textarea{width:100%;border:1.5px solid #D6D0C8;border-radius:10px;padding:.7rem .9rem;font-family:'DM Sans',sans-serif;font-size:.85rem;color:#1A1714;resize:vertical;min-height:160px}
.modal textarea:focus{outline:none;border-color:#B8963E}
.modal-footer{display:flex;justify-content:flex-end;gap:.6rem;margin-top:1.25rem}

/* ── TOAST ── */
.toast{position:fixed;bottom:1.5rem;right:1.5rem;background:#1A1714;color:#fff;padding:.75rem 1.25rem;border-radius:10px;font-size:.82rem;box-shadow:0 4px 20px rgba(0,0,0,.2);z-index:100;display:flex;align-items:center;gap:.5rem}
.toast.success{background:#1A5E2E}
.toast.error{background:#7A1A1A}
</style>
</head>
<body x-data="adminPanel()" x-init="init()">

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-left">
        <div class="topbar-dot"></div>
        <div>
            <h1><?= esc($promocion['nombre']) ?></h1>
            <p><?= esc($promocion['nombre_colegio'] ?? '') ?><?= $promocion['nivel'] ? ' · ' . esc($promocion['nivel']) : '' ?></p>
        </div>
    </div>
    <div class="topbar-actions">
        <a href="<?= base_url('admin/formularios/exportar/' . $promocion['id']) ?>" class="btn btn-gold">⬇ Exportar CSV</a>
        <button class="btn btn-dark" @click="abrirImportar()">+ Importar alumnos</button>
        <a href="<?= base_url('admin/formularios') ?>" class="btn btn-light">← Volver</a>
    </div>
</div>

<div class="container">

    <!-- ESTADÍSTICAS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num"><?= $promocion['total_alumnos'] ?></div>
            <div class="stat-label">Total alumnos</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color:#1A5E2E"><?= $promocion['completados'] ?></div>
            <div class="stat-label">Completados</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color:#7A5000"><?= $promocion['pendientes'] ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-num gold"><?= $stock['cuadros'] ?></div>
            <div class="stat-label">Cuadros libres</div>
        </div>
        <div class="stat-card">
            <div class="stat-num gold"><?= $stock['anuarios'] ?></div>
            <div class="stat-label">Anuarios libres</div>
        </div>
    </div>

    <!-- PROGRESO -->
    <?php
        $pct = $promocion['total_alumnos'] > 0
            ? round(($promocion['completados'] / $promocion['total_alumnos']) * 100)
            : 0;
    ?>
    <div class="progress-section">
        <div class="prog-header">
            <span>Progreso de formularios</span>
            <strong><?= $pct ?>%</strong>
        </div>
        <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
    </div>

    <!-- BÚSQUEDA + TABLA -->
    <div class="search-bar">
        <input type="text" x-model="busqueda" placeholder="Buscar por nombre...">
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Enlace</th>
                    <th style="text-align:right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alumnos as $i => $a): ?>
                <tr x-show="filtra('<?= esc($a['nombre']) ?>')" x-cloak>
                    <td style="color:#9E9488"><?= $i + 1 ?></td>
                    <td style="font-weight:500"><?= esc($a['nombre']) ?></td>
                    <td>
                        <?php if ($a['completado']): ?>
                            <span class="badge badge-green">✓ Completado</span>
                        <?php else: ?>
                            <span class="badge badge-amber">⏳ Pendiente</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="link-text" title="<?= base_url('formulario/' . $a['token']) ?>">
                            <?= base_url('formulario/' . $a['token']) ?>
                        </span>
                    </td>
                    <td style="text-align:right">
                        <button class="btn btn-light btn-sm"
                            @click="copiarLink('<?= base_url('formulario/' . $a['token']) ?>')">
                            📋 Copiar
                        </button>
                        <?php if (!$a['completado']): ?>
                        <a href="<?= base_url('formulario/' . $a['token']) ?>"
                           target="_blank" class="btn btn-dark btn-sm" style="margin-left:.3rem">
                            👁 Ver
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- MODAL IMPORTAR -->
<div class="overlay" x-show="modalImportar" x-cloak @click.self="modalImportar = false">
    <div class="modal" @click.stop>
        <h2>Importar alumnos</h2>
        <p>Escribe un nombre por línea. Se generará un enlace único para cada alumno.</p>
        <textarea x-model="nombresTexto" placeholder="Juan García Pérez&#10;María López Torres&#10;Carlos Mendoza Lima"></textarea>
        <div class="modal-footer">
            <button class="btn btn-light" @click="modalImportar = false">Cancelar</button>
            <button class="btn btn-gold" @click="importar()" :disabled="importando">
                <span x-show="!importando">Importar</span>
                <span x-show="importando">Importando...</span>
            </button>
        </div>
    </div>
</div>

<!-- TOAST -->
<div class="toast" :class="toast.tipo" x-show="toast.visible" x-cloak x-text="toast.msg"></div>

<script>
const BASE_URL  = "<?= base_url('') ?>";
const PROM_ID   = <?= (int) $promocion['id'] ?>;

function adminPanel() {
    return {
        busqueda:      '',
        modalImportar: false,
        nombresTexto:  '',
        importando:    false,
        toast:         { visible: false, msg: '', tipo: 'success' },

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
                .split('\n')
                .map(n => n.trim())
                .filter(n => n.length > 0);

            if (nombres.length === 0) {
                this.showToast('Escribe al menos un nombre.', 'error');
                return;
            }

            this.importando = true;
            try {
                const r = await fetch(BASE_URL + 'admin/formularios/alumno/importar', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ promocion_id: PROM_ID, nombres }),
                });
                const data = await r.json();

                if (data.ok) {
                    this.showToast(data.insertados.length + ' alumno(s) importados correctamente.', 'success');
                    this.modalImportar = false;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.showToast(data.error || 'Error al importar.', 'error');
                }
            } catch (e) {
                this.showToast('Error de conexión.', 'error');
            } finally {
                this.importando = false;
            }
        },

        async copiarLink(url) {
            try {
                await navigator.clipboard.writeText(url);
                this.showToast('¡Enlace copiado!', 'success');
            } catch(e) {
                this.showToast('No se pudo copiar. Copia manualmente.', 'error');
            }
        },

        showToast(msg, tipo) {
            this.toast = { visible: true, msg, tipo };
            setTimeout(() => { this.toast.visible = false; }, 3000);
        },
    };
}
</script>
</body>
</html>
