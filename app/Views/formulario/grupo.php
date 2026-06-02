<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulario — <?= esc($promocion['nombre']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:#F7F4EE;color:#1A1714;font-size:15px;line-height:1.6}
.header{background:#1A1714;padding:1.1rem 1.5rem;display:flex;align-items:center;gap:.75rem}
.header-dot{width:10px;height:10px;border-radius:50%;background:#B8963E;flex-shrink:0}
.header h1{font-family:'Playfair Display',serif;font-size:1.1rem;color:#F7F4EE;font-weight:700;letter-spacing:.01em}
.header p{font-size:.72rem;color:#B8963E;letter-spacing:.1em;text-transform:uppercase;margin-top:1px}
.stock-banner{background:#fff;border-bottom:2px solid #F7F4EE;padding:.75rem 1.5rem;display:flex;gap:1.25rem;flex-wrap:wrap}
.stock-item{display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:#4A4440}
.stock-num{font-weight:700;font-size:1rem;color:#1A1714}
.stock-num.agotado{color:#c0392b}
.stock-badge{background:#B8963E;color:#fff;font-size:.68rem;font-weight:600;padding:.2rem .5rem;border-radius:4px;letter-spacing:.05em}
.stock-badge.agotado{background:#c0392b}
.container{max-width:680px;margin:0 auto;padding:1.75rem 1.25rem 4rem}
.section{background:#fff;border-radius:16px;padding:1.75rem 1.5rem;margin-bottom:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.section-title{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:#1A1714;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px solid #F7F4EE;display:flex;align-items:center;gap:.6rem}
.section-title span{width:24px;height:24px;background:#B8963E;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.78rem;font-weight:600;color:#4A4440;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem}
.field input,.field select{width:100%;background:#F7F4EE;border:1.5px solid #E4E0D8;border-radius:10px;padding:.65rem .9rem;font-family:'DM Sans',sans-serif;font-size:.9rem;color:#1A1714;transition:border-color .18s,box-shadow .18s}
.field input:focus,.field select:focus{outline:none;border-color:#B8963E;box-shadow:0 0 0 3px rgba(184,150,62,.12)}
.field input.err,.field select.err{border-color:#e74c3c}
.field .hint{font-size:.72rem;color:#999;margin-top:.25rem}
.field .err-msg{font-size:.72rem;color:#e74c3c;margin-top:.25rem;display:flex;align-items:center;gap:3px}
.readonly-val{background:#ECEAE4;border:1.5px solid #D6D0C8;border-radius:10px;padding:.65rem .9rem;font-size:.9rem;color:#6B6460}
.productos-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:.5rem}
@media(max-width:440px){.productos-grid{grid-template-columns:1fr}}
.producto-card{border:2px solid #E4E0D8;border-radius:14px;padding:1.2rem 1rem;cursor:pointer;transition:border-color .18s,background .18s,opacity .18s;user-select:none;position:relative}
.producto-card.selected{border-color:#B8963E;background:#FBF7EE}
.producto-card.disabled{opacity:.45;cursor:not-allowed;pointer-events:none}
.producto-card .prod-icon{font-size:2rem;margin-bottom:.5rem;display:block}
.producto-card .prod-name{font-weight:700;font-size:.95rem;color:#1A1714;margin-bottom:.2rem}
.producto-card .prod-stock{font-size:.75rem;color:#6B6460}
.producto-card .prod-stock.agotado{color:#c0392b;font-weight:600}
.prod-check{position:absolute;top:.7rem;right:.7rem;width:22px;height:22px;border:2px solid #D6D0C8;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;background:#fff;transition:all .15s}
.producto-card.selected .prod-check{background:#B8963E;border-color:#B8963E;color:#fff}
.subfield{background:#FBF7EE;border-radius:10px;padding:.9rem 1rem;margin-top:.5rem;border:1.5px solid #EDE8DC}
.check-item{display:flex;gap:.75rem;align-items:flex-start;padding:.75rem 0;border-bottom:1px solid #F7F4EE}
.check-item:last-child{border-bottom:none}
.check-item input[type=checkbox]{width:18px;height:18px;accent-color:#B8963E;flex-shrink:0;margin-top:2px;cursor:pointer}
.check-item label{font-size:.85rem;color:#4A4440;cursor:pointer;line-height:1.5}
.check-item label strong{color:#1A1714}
.btn-submit{width:100%;background:#B8963E;color:#fff;border:none;border-radius:12px;padding:.9rem;font-family:'DM Sans',sans-serif;font-size:1rem;font-weight:600;cursor:pointer;transition:opacity .2s,transform .15s;box-shadow:0 4px 16px rgba(184,150,62,.3);letter-spacing:.02em}
.btn-submit:hover:not(:disabled){opacity:.9;transform:translateY(-1px)}
.btn-submit:disabled{opacity:.5;cursor:not-allowed}
.alert{border-radius:10px;padding:.75rem 1rem;font-size:.85rem;margin-bottom:1rem;display:flex;gap:.6rem;align-items:flex-start}
.alert-err{background:#fdf0f0;border:1px solid #f5c6cb;color:#842029}
.alert-ok{background:#eafaf1;border:1px solid #a3d9b1;color:#145a30}
</style>
</head>
<body x-data="formularioGrupal()" x-init="init()">

<div class="header">
    <div class="header-dot"></div>
    <div>
        <h1>Quique Ronceros el Fotógrafo</h1>
        <p><?= esc($promocion['nombre']) ?></p>
    </div>
</div>

<div class="stock-banner">
    <div class="stock-item">
        <span>📷 Cuadros disponibles:</span>
        <span class="stock-num" :class="stock.cuadros === 0 ? 'agotado' : ''" x-text="stock.cuadros"></span>
        <span class="stock-badge" :class="stock.cuadros === 0 ? 'agotado' : ''" x-text="stock.cuadros === 0 ? 'AGOTADO' : 'DISPONIBLE'"></span>
    </div>
    <div class="stock-item">
        <span>📚 Anuarios disponibles:</span>
        <span class="stock-num" :class="stock.anuarios === 0 ? 'agotado' : ''" x-text="stock.anuarios"></span>
        <span class="stock-badge" :class="stock.anuarios === 0 ? 'agotado' : ''" x-text="stock.anuarios === 0 ? 'AGOTADO' : 'DISPONIBLE'"></span>
    </div>
</div>

<div class="container">
    <div class="alert alert-err" x-show="errorGlobal" x-text="errorGlobal" x-cloak></div>
    <div class="alert alert-ok" x-show="enviado" x-cloak>¡Formulario enviado! Redirigiendo...</div>

    <!-- SECCIÓN 1: ALUMNO -->
    <div class="section">
        <div class="section-title"><span>1</span> Datos del alumno</div>

        <div class="field">
            <label>Nombre completo *</label>
            <input type="text" x-model="form.nombre_alumno" :class="errors.nombre_alumno ? 'err' : ''"
                   placeholder="Nombres y apellidos">
            <div class="err-msg" x-show="errors.nombre_alumno" x-text="errors.nombre_alumno"></div>
        </div>
        <div class="field">
            <label>Fecha de nacimiento *</label>
            <input type="date" x-model="form.fecha_nacimiento" :class="errors.fecha_nacimiento ? 'err' : ''">
            <div class="err-msg" x-show="errors.fecha_nacimiento" x-text="errors.fecha_nacimiento"></div>
        </div>
        <div class="field">
            <label>Color favorito</label>
            <input type="text" x-model="form.color_favorito" placeholder="Ej: Azul marino">
        </div>
        <div class="field">
            <label>¿Qué quieres ser de grande?</label>
            <input type="text" x-model="form.profesion_futura" placeholder="Ej: Médico, Ingeniero, Artista...">
        </div>
    </div>

    <!-- SECCIÓN 2: TUTOR -->
    <div class="section">
        <div class="section-title"><span>2</span> Datos del tutor / apoderado</div>

        <div class="field">
            <label>Nombre completo del tutor *</label>
            <input type="text" x-model="form.nombre_tutor" :class="errors.nombre_tutor ? 'err' : ''"
                   placeholder="Nombres y apellidos">
            <div class="err-msg" x-show="errors.nombre_tutor" x-text="errors.nombre_tutor"></div>
        </div>
        <div class="field">
            <label>Relación con el alumno *</label>
            <select x-model="form.relacion_tutor">
                <option value="Padre">Padre</option>
                <option value="Madre">Madre</option>
                <option value="Tutor">Tutor Legal</option>
            </select>
        </div>
        <div class="field">
            <label>Teléfono / WhatsApp *</label>
            <input type="tel" x-model="form.telefono" :class="errors.telefono ? 'err' : ''"
                   placeholder="Ej: 987654321">
            <div class="err-msg" x-show="errors.telefono" x-text="errors.telefono"></div>
        </div>
        <div class="field">
            <label>Correo electrónico</label>
            <input type="email" x-model="form.email" placeholder="tumail@ejemplo.com">
            <div class="hint">Opcional — para enviar novedades</div>
        </div>
    </div>

    <!-- SECCIÓN 3: COLEGIO (solo lectura) -->
    <div class="section">
        <div class="section-title"><span>3</span> Datos del colegio</div>
        <div class="field">
            <label>Colegio</label>
            <div class="readonly-val"><?= esc($promocion['nombre_colegio'] ?? '—') ?></div>
        </div>
        <div class="field">
            <label>Promoción / Grado</label>
            <div class="readonly-val"><?= esc($promocion['nombre']) ?><?= $promocion['nivel'] ? ' — ' . esc($promocion['nivel']) : '' ?></div>
        </div>
    </div>

    <!-- SECCIÓN 4: PRODUCTOS -->
    <div class="section">
        <div class="section-title"><span>4</span> Productos</div>
        <p style="font-size:.82rem;color:#6B6460;margin-bottom:1.1rem">Selecciona lo que deseas adquirir.</p>

        <div class="productos-grid">
            <div class="producto-card"
                 :class="{ selected: form.tiene_cuadro, disabled: stock.cuadros === 0 }"
                 @click="stock.cuadros > 0 && (form.tiene_cuadro = !form.tiene_cuadro)">
                <span class="prod-check" x-text="form.tiene_cuadro ? '✓' : ''"></span>
                <span class="prod-icon">🖼️</span>
                <div class="prod-name">Cuadro escolar</div>
                <div class="prod-stock" :class="stock.cuadros === 0 ? 'agotado' : ''" x-text="stock.cuadros === 0 ? 'Sin stock' : stock.cuadros + ' disponibles'"></div>
            </div>
            <div class="producto-card"
                 :class="{ selected: form.tiene_anuario, disabled: stock.anuarios === 0 }"
                 @click="stock.anuarios > 0 && (form.tiene_anuario = !form.tiene_anuario)">
                <span class="prod-check" x-text="form.tiene_anuario ? '✓' : ''"></span>
                <span class="prod-icon">📖</span>
                <div class="prod-name">Anuario</div>
                <div class="prod-stock" :class="stock.anuarios === 0 ? 'agotado' : ''" x-text="stock.anuarios === 0 ? 'Sin stock' : stock.anuarios + ' disponibles'"></div>
            </div>
        </div>

        <div class="subfield" x-show="form.tiene_cuadro" x-transition>
            <div class="field" style="margin-bottom:0">
                <label>Tamaño del cuadro *</label>
                <select x-model="form.cuadro_tamano" :class="errors.cuadro_tamano ? 'err' : ''">
                    <option value="">— Selecciona un tamaño —</option>
                    <option value="20x30 cm">20×30 cm</option>
                    <option value="30x40 cm">30×40 cm</option>
                    <option value="40x50 cm">40×50 cm</option>
                    <option value="50x60 cm">50×60 cm</option>
                </select>
                <div class="err-msg" x-show="errors.cuadro_tamano" x-text="errors.cuadro_tamano"></div>
            </div>
        </div>
        <div class="subfield" x-show="form.tiene_anuario" x-transition style="margin-top:.75rem">
            <div class="field" style="margin-bottom:0">
                <label>Modelo de anuario *</label>
                <select x-model="form.anuario_modelo" :class="errors.anuario_modelo ? 'err' : ''">
                    <option value="">— Selecciona un modelo —</option>
                    <option value="Clásico Tapa Dura">Clásico Tapa Dura</option>
                    <option value="Premium Cuero">Premium Cuero</option>
                    <option value="Digital + Físico">Digital + Físico</option>
                </select>
                <div class="err-msg" x-show="errors.anuario_modelo" x-text="errors.anuario_modelo"></div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 5: AUTORIZACIONES -->
    <div class="section">
        <div class="section-title"><span>5</span> Autorizaciones</div>
        <div class="check-item">
            <input type="checkbox" id="ck-img" x-model="form.acepta_imagenes">
            <label for="ck-img"><strong>Autorizo el uso de imágenes.</strong> Doy mi consentimiento para que las fotografías del alumno sean utilizadas con fines de muestra del trabajo fotográfico de Quique Ronceros.</label>
        </div>
        <div class="check-item">
            <input type="checkbox" id="ck-datos" x-model="form.acepta_datos">
            <label for="ck-datos"><strong>Acepto la política de datos.</strong> Autorizo el tratamiento de mis datos personales conforme a la normativa vigente de protección de datos.</label>
        </div>
        <div class="err-msg" style="margin-top:.5rem" x-show="errors.acepta_datos" x-text="errors.acepta_datos"></div>
    </div>

    <button class="btn-submit" @click="enviar" :disabled="enviando || enviado">
        <span x-show="!enviando">✉️ Enviar formulario</span>
        <span x-show="enviando">Enviando...</span>
    </button>
</div>

<script>
const BASE_URL         = "<?= base_url('') ?>";
const PROM_ID          = <?= (int) $promocion['id'] ?>;
const TOKEN_COMPARTIDO = "<?= esc($token) ?>";

function formularioGrupal() {
    return {
        stock: { cuadros: <?= (int) $stock['cuadros'] ?>, anuarios: <?= (int) $stock['anuarios'] ?> },
        form: {
            nombre_alumno: '', fecha_nacimiento: '', color_favorito: '', profesion_futura: '',
            nombre_tutor: '', relacion_tutor: 'Padre', telefono: '', email: '',
            tiene_cuadro: false, cuadro_tamano: '', tiene_anuario: false, anuario_modelo: '',
            acepta_imagenes: false, acepta_datos: false,
        },
        errors: {}, errorGlobal: '', enviando: false, enviado: false,

        init() { setInterval(() => this.actualizarStock(), 30000); },

        async actualizarStock() {
            try {
                const r = await fetch(BASE_URL + 'formulario/stock/' + PROM_ID);
                const d = await r.json();
                if (d.ok) {
                    this.stock = d.stock;
                    if (this.stock.cuadros  === 0) this.form.tiene_cuadro  = false;
                    if (this.stock.anuarios === 0) this.form.tiene_anuario = false;
                }
            } catch(e) {}
        },

        validar() {
            const e = {};
            if (!this.form.nombre_alumno.trim())    e.nombre_alumno    = 'El nombre del alumno es obligatorio.';
            if (!this.form.fecha_nacimiento)         e.fecha_nacimiento = 'La fecha de nacimiento es obligatoria.';
            if (!this.form.nombre_tutor.trim())      e.nombre_tutor     = 'El nombre del tutor es obligatorio.';
            if (!this.form.telefono.trim())          e.telefono         = 'El teléfono es obligatorio.';
            if (this.form.tiene_cuadro  && !this.form.cuadro_tamano)  e.cuadro_tamano  = 'Selecciona el tamaño del cuadro.';
            if (this.form.tiene_anuario && !this.form.anuario_modelo) e.anuario_modelo = 'Selecciona el modelo del anuario.';
            if (!this.form.acepta_datos) e.acepta_datos = 'Debes aceptar la política de datos.';
            this.errors = e;
            return Object.keys(e).length === 0;
        },

        async enviar() {
            this.errorGlobal = '';
            if (!this.validar()) {
                this.$nextTick(() => {
                    const el = document.querySelector('.err-msg');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                return;
            }
            this.enviando = true;
            try {
                const r = await fetch(BASE_URL + 'formulario/grupo/guardar', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ ...this.form, token_compartido: TOKEN_COMPARTIDO }),
                });
                const data = await r.json();
                if (data.ok) {
                    this.enviado = true;
                    setTimeout(() => { window.location.href = BASE_URL + 'formulario/gracias'; }, 1200);
                } else {
                    this.errorGlobal = data.error || 'Error al enviar. Intenta de nuevo.';
                }
            } catch(e) {
                this.errorGlobal = 'Error de conexión. Verifica tu internet.';
            } finally {
                this.enviando = false;
            }
        },
    };
}
</script>
</body>
</html>
