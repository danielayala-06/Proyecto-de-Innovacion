<?php
$cuadrosEnContrato  = $productosInfo['tiene_cuadros'];
$anuariosEnContrato = $productosInfo['tiene_anuarios'];
$mostrarProductos   = $productosInfo['mostrar_seleccion'];
$sNum = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulario — <?= esc($promocion['nombre']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="<?= base_url('css/formulario.css') ?>">
</head>
<body x-data="formularioGrupal()" x-init="init()">

<div class="header">
    <div class="header-dot"></div>
    <div>
        <h1>Quique Ronceros el Fotógrafo</h1>
        <p><?= esc($promocion['nombre']) ?></p>
    </div>
</div>

<!-- STOCK EN TIEMPO REAL (solo productos del contrato) -->
<?php if ($cuadrosEnContrato || $anuariosEnContrato): ?>
<div class="stock-banner">
    <?php if ($cuadrosEnContrato): ?>
    <div class="stock-item">
        <span><i class="bi bi-image" style="color:#B8963E"></i> Cuadros disponibles:</span>
        <span class="stock-num" :class="stock.cuadros === 0 ? 'agotado' : ''" x-text="stock.cuadros"></span>
        <span class="stock-badge" :class="stock.cuadros === 0 ? 'agotado' : ''" x-text="stock.cuadros === 0 ? 'AGOTADO' : 'DISPONIBLE'"></span>
    </div>
    <?php endif; ?>
    <?php if ($anuariosEnContrato): ?>
    <div class="stock-item">
        <span><i class="bi bi-journal-text" style="color:#B8963E"></i> Anuarios disponibles:</span>
        <span class="stock-num" :class="stock.anuarios === 0 ? 'agotado' : ''" x-text="stock.anuarios"></span>
        <span class="stock-badge" :class="stock.anuarios === 0 ? 'agotado' : ''" x-text="stock.anuarios === 0 ? 'AGOTADO' : 'DISPONIBLE'"></span>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="container">
    <div class="alert alert-err" x-show="errorGlobal" x-text="errorGlobal" x-cloak></div>
    <div class="alert alert-ok" x-show="enviado" x-cloak>¡Formulario enviado! Redirigiendo...</div>

    <!-- SECCIÓN 1: ALUMNO -->
    <div class="section">
        <div class="section-title"><span><?= ++$sNum ?></span> Datos del alumno</div>

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
        <div class="section-title"><span><?= ++$sNum ?></span> Datos del tutor / apoderado</div>

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

    <!-- SECCIÓN PRODUCTOS (solo si hay 2+ tipos en el contrato) -->
    <?php if ($cuadrosEnContrato || $anuariosEnContrato): ?>
    <div class="section">
        <div class="section-title"><span><?= ++$sNum ?></span> Productos</div>

        <?php if ($mostrarProductos): ?>
        <!-- Hay cuadros Y anuarios en el contrato: el padre elige -->
        <p style="font-size:.82rem;color:#6B6460;margin-bottom:1.1rem">Selecciona el producto que deseas adquirir para tu hijo.</p>

        <div class="productos-grid">
            <div class="producto-card"
                 :class="{ selected: form.tiene_cuadro, disabled: stock.cuadros === 0 }"
                 @click="stock.cuadros > 0 && (form.tiene_cuadro = !form.tiene_cuadro)">
                <span class="prod-check"><i class="bi bi-check-lg" x-show="form.tiene_cuadro"></i></span>
                <i class="bi bi-image-fill prod-icon"></i>
                <div class="prod-name">Cuadro escolar</div>
                <div class="prod-stock" :class="stock.cuadros === 0 ? 'agotado' : ''" x-text="stock.cuadros === 0 ? 'Sin stock' : stock.cuadros + ' disponibles'"></div>
            </div>
            <div class="producto-card"
                 :class="{ selected: form.tiene_anuario, disabled: stock.anuarios === 0 }"
                 @click="stock.anuarios > 0 && (form.tiene_anuario = !form.tiene_anuario)">
                <span class="prod-check"><i class="bi bi-check-lg" x-show="form.tiene_anuario"></i></span>
                <i class="bi bi-journal-text prod-icon"></i>
                <div class="prod-name">Anuario</div>
                <div class="prod-stock" :class="stock.anuarios === 0 ? 'agotado' : ''" x-text="stock.anuarios === 0 ? 'Sin stock' : stock.anuarios + ' disponibles'"></div>
            </div>
        </div>

        <?php else: ?>
        <!-- El paquete define el producto del alumno; no hay elección -->
        <?php if ($cuadrosEnContrato && $anuariosEnContrato): ?>
        <div class="prod-unico">
            <span class="prod-unico-icon"><i class="bi bi-image-fill"></i><i class="bi bi-journal-text"></i></span>
            <div>
                <div class="prod-unico-name">Cuadro escolar + Anuario</div>
                <div class="prod-unico-label">Ambos productos incluidos en tu paquete — selecciona el producto sin elegir tamaño o modelo.</div>
            </div>
        </div>
        <?php elseif ($cuadrosEnContrato): ?>
        <div class="prod-unico">
            <span class="prod-unico-icon"><i class="bi bi-image-fill"></i></span>
            <div>
                <div class="prod-unico-name">Cuadro escolar</div>
                <div class="prod-unico-label">Producto incluido en tu contrato — el tamaño es opcional.</div>
            </div>
        </div>
        <?php else: ?>
        <div class="prod-unico">
            <span class="prod-unico-icon"><i class="bi bi-journal-text"></i></span>
            <div>
                <div class="prod-unico-name">Anuario</div>
                <div class="prod-unico-label">Producto incluido en tu contrato — el modelo es opcional.</div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($cuadrosEnContrato): ?>
        <div class="subfield" x-show="form.tiene_cuadro" x-transition>
            <div class="field" style="margin-bottom:0">
                <label>Tamaño del cuadro</label>
                <select x-model="form.cuadro_tamano" :class="errors.cuadro_tamano ? 'err' : ''">
                    <option value="">— Selecciona un tamaño (opcional) —</option>
                    <option value="20x30 cm">20×30 cm</option>
                    <option value="30x40 cm">30×40 cm</option>
                    <option value="40x50 cm">40×50 cm</option>
                    <option value="50x60 cm">50×60 cm</option>
                </select>
                <div class="hint">Opcional — completa solo si ya tienes preferencia.</div>
                <div class="err-msg" x-show="errors.cuadro_tamano" x-text="errors.cuadro_tamano"></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($anuariosEnContrato): ?>
        <div class="subfield" x-show="form.tiene_anuario" x-transition style="margin-top:.75rem">
            <div class="field" style="margin-bottom:0">
                <label>Modelo de anuario</label>
                <select x-model="form.anuario_modelo" :class="errors.anuario_modelo ? 'err' : ''">
                    <option value="">— Selecciona un modelo (opcional) —</option>
                    <option value="Clásico Tapa Dura">Clásico Tapa Dura</option>
                    <option value="Premium Cuero">Premium Cuero</option>
                    <option value="Digital + Físico">Digital + Físico</option>
                </select>
                <div class="hint">Opcional — completa solo si deseas elegir modelo.</div>
                <div class="err-msg" x-show="errors.anuario_modelo" x-text="errors.anuario_modelo"></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- SECCIÓN AUTORIZACIONES -->
    <div class="section">
        <div class="section-title"><span><?= ++$sNum ?></span> Autorizaciones</div>
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
        <span x-show="!enviando"><i class="bi bi-send"></i> Enviar formulario</span>
        <span x-show="enviando">Enviando...</span>
    </button>
</div>

<script>
const BASE_URL         = "<?= base_url('') ?>";
const PROM_ID          = <?= (int) $promocion['id'] ?>;
const TOKEN_COMPARTIDO = "<?= esc($token) ?>";
const FORMULARIO_GRUPAL_KEY = `formulario-grupal-${TOKEN_COMPARTIDO}`;

const CUADROS_EN_CONTRATO  = <?= $cuadrosEnContrato  ? 'true' : 'false' ?>;
const ANUARIOS_EN_CONTRATO = <?= $anuariosEnContrato ? 'true' : 'false' ?>;
const MOSTRAR_PRODUCTOS    = <?= $mostrarProductos   ? 'true' : 'false' ?>;

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

        init() {
            if (localStorage.getItem(FORMULARIO_GRUPAL_KEY) === '1') {
                window.location.href = BASE_URL + 'formulario/gracias';
                return;
            }

            // Cuando no hay selección, los productos del paquete se asignan automáticamente
            if (!MOSTRAR_PRODUCTOS) {
                if (CUADROS_EN_CONTRATO)  this.form.tiene_cuadro  = true;
                if (ANUARIOS_EN_CONTRATO) this.form.tiene_anuario = true;
            }
            setInterval(() => this.actualizarStock(), 30000);
        },

        async actualizarStock() {
            try {
                const r = await fetch(BASE_URL + 'formulario/stock/' + PROM_ID);
                const d = await r.json();
                if (d.ok) {
                    this.stock = d.stock;
                    // Solo desmarcar si el padre puede elegir (hay 2+ tipos en el contrato)
                    if (MOSTRAR_PRODUCTOS) {
                        if (this.stock.cuadros  === 0) this.form.tiene_cuadro  = false;
                        if (this.stock.anuarios === 0) this.form.tiene_anuario = false;
                    }
                }
            } catch(e) {}
        },

        validar() {
            const e = {};
            if (!this.form.nombre_alumno.trim())    e.nombre_alumno    = 'El nombre del alumno es obligatorio.';
            if (!this.form.fecha_nacimiento)         e.fecha_nacimiento = 'La fecha de nacimiento es obligatoria.';
            if (!this.form.nombre_tutor.trim())      e.nombre_tutor     = 'El nombre del tutor es obligatorio.';
            if (!this.form.telefono.trim())          e.telefono         = 'El teléfono es obligatorio.';
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
                    localStorage.setItem(FORMULARIO_GRUPAL_KEY, '1');
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
