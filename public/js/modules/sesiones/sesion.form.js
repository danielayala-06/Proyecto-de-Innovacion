/**
 * @file    sesion.form.js
 * @module  modules/sesiones/form
 *
 * Gestiona los formularios de creación/edición de sesiones y de registro
 * de nuevos estudiantes en el módulo de sesiones fotográficas.
 *
 * Expone dos objetos:
 *  - {@link sesionForm}     — Opera sobre el modal `#modalSesion`.
 *  - {@link estudianteForm} — Opera sobre el modal `#modalEstudiante`.
 *
 * Campos del formulario de sesión (IDs):
 *  `sfId`, `sfTipo`, `sfFecha`, `sfHora`, `sfObservaciones`, `sfPromocion`
 *
 * Campos del formulario de estudiante (IDs):
 *  `efNombres`, `efApellidos`, `efNacAnio`, `efNacMes`, `efNacDia`,
 *  `efColor`, `efProfesion`,
 *  `apNombres`, `apApellidos`, `apTelefono`, `apDocTipo`, `apDocNum`,
 *  `apCorreo`, `apRelacion`
 */

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS DE FECHA (3 selects → YYYY-MM-DD)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Construye una fecha ISO "YYYY-MM-DD" desde tres selects (año, mes, día).
 * Devuelve null si alguno está vacío.
 */
function _buildFecha(idAnio, idMes, idDia) {
    const a = document.getElementById(idAnio)?.value || '';
    const m = document.getElementById(idMes)?.value  || '';
    const d = document.getElementById(idDia)?.value  || '';
    return (a && m && d) ? `${a}-${m}-${d}` : null;
}

/**
 * Valida los tres selects de fecha de nacimiento.
 * Comprueba:
 *   - Relleno parcial (todos o ninguno).
 *   - Fecha inexistente (ej. 29-feb en año no bisiesto).
 *   - Fecha futura o más antigua que maxAnios.
 *
 * @param {string} idAnio
 * @param {string} idMes
 * @param {string} idDia
 * @param {number} maxAnios  Máximo de años atrás permitidos.
 * @returns {string|null}    Mensaje de error o null si es válida.
 */
function _validarFecha(idAnio, idMes, idDia, maxAnios) {
    const a = document.getElementById(idAnio)?.value || '';
    const m = document.getElementById(idMes)?.value  || '';
    const d = document.getElementById(idDia)?.value  || '';

    const llenos = [a, m, d].filter(Boolean).length;
    if (llenos > 0 && llenos < 3) return 'Selecciona día, mes y año de nacimiento.';
    if (!llenos) return null; // fecha opcional — sin problema

    const fn  = new Date(`${a}-${m}-${d}T00:00:00`);
    // Detecta desbordamiento de mes (ej. 30-feb → 01-mar en JS)
    if (isNaN(fn.getTime()) || fn.getDate() !== parseInt(d, 10)) {
        return `La fecha ${d}/${m}/${a} no existe (¿año bisiesto?).`;
    }

    const hoy = new Date(); hoy.setHours(0, 0, 0, 0);
    const min = new Date(hoy.getFullYear() - maxAnios, hoy.getMonth(), hoy.getDate());
    if (fn > hoy) return 'La fecha de nacimiento no puede ser en el futuro.';
    if (fn < min) return `El estudiante no puede tener más de ${maxAnios} años.`;
    return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// FORMULARIO DE SESIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Colección de operaciones del formulario de nueva/editar sesión.
 *
 * @namespace sesionForm
 */
export const sesionForm = {

    /**
     * Limpia todos los campos del formulario de sesión.
     * Opcionalmente pre-selecciona un tipo de sesión (útil al abrir desde
     * el botón de una barra de límite específica).
     *
     * @param {string} [tipo=''] - Valor inicial del select `#sfTipo`.
     * @returns {void}
     */
    limpiar(tipo = '') {
        document.getElementById('sfId').value            = '';
        document.getElementById('sfTipo').value          = tipo;
        document.getElementById('sfFecha').value         = '';
        document.getElementById('sfHora').value          = '07:00';
        document.getElementById('sfObservaciones').value = '';

        // Restricciones de fecha: hoy → +18 meses
        const hoy = new Date();
        const max = new Date(hoy);
        max.setMonth(max.getMonth() + 18);
        const toISO = d => d.toISOString().split('T')[0];
        const sfFecha = document.getElementById('sfFecha');
        sfFecha.min = toISO(hoy);
        sfFecha.max = toISO(max);
    },

    /**
     * Pobla el formulario con los datos de una sesión existente para edición.
     * Descompone `fecha_hora_sesion` en campos separados de fecha y hora.
     *
     * @param {Object} sesion                      - Objeto sesión de la API.
     * @param {number} sesion.id_sesion
     * @param {string} sesion.tipo
     * @param {string} sesion.fecha_hora_sesion    - Formato `'YYYY-MM-DD HH:MM:SS'`.
     * @param {string|null} [sesion.observaciones]
     * @returns {void}
     */
    poblar(sesion) {
        document.getElementById('sfId').value   = sesion.id_sesion;
        document.getElementById('sfTipo').value = sesion.tipo;
        const [fecha, hora] = sesion.fecha_hora_sesion.split(' ');
        document.getElementById('sfFecha').value         = fecha;
        document.getElementById('sfHora').value          = hora?.slice(0, 5) ?? '';
        document.getElementById('sfObservaciones').value = sesion.observaciones ?? '';
    },

    /**
     * Lee los valores del formulario y construye el payload para la API.
     * Si no se indicó hora, usa `'09:00'` como valor por defecto.
     *
     * @returns {{
     *   id_promocion:      number,
     *   tipo:              string,
     *   fecha_hora_sesion: string,
     *   observaciones:     string|null
     * }}
     */
    datos() {
        const fecha = document.getElementById('sfFecha').value;
        const hora  = document.getElementById('sfHora').value || '09:00';
        return {
            id_promocion:      parseInt(document.getElementById('sfPromocion').value, 10),
            tipo:              document.getElementById('sfTipo').value,
            fecha_hora_sesion: `${fecha} ${hora}:00`,
            observaciones:     document.getElementById('sfObservaciones').value.trim() || null,
        };
    },

    /**
     * Valida los campos requeridos del formulario de sesión.
     *
     * @returns {string|null} Mensaje de error si falla la validación, o `null` si es válido.
     */
    validar() {
        const tipo  = document.getElementById('sfTipo').value;
        const fecha = document.getElementById('sfFecha').value;
        const hora  = document.getElementById('sfHora').value || '09:00';

        if (!tipo)  return 'Selecciona el tipo de sesión.';
        if (!fecha) return 'La fecha es obligatoria.';

        const dt    = new Date(`${fecha}T${hora}`);
        const ahora = new Date();

        if (dt <= ahora) return 'No es posible agendar una sesión en una fecha y hora ya transcurridas.';

        const max = new Date();
        max.setMonth(max.getMonth() + 10);
        if (dt > max) return 'La sesión no puede programarse con más de 18 meses de anticipación.';

        const h = dt.getHours();
        if (h < 7 || h >= 22) return 'El horario debe ser entre las 7:00 a.m. y las 10:00 p.m.';

        return null;
    },

    /**
     * Devuelve el ID de la sesión del campo oculto `#sfId`, o `null` si está vacío.
     * Permite distinguir entre modo creación (`null`) y modo edición.
     *
     * @returns {number|null}
     */
    getId() {
        return parseInt(document.getElementById('sfId').value, 10) || null;
    },
};

// ─────────────────────────────────────────────────────────────────────────────
// FORMULARIO DE ESTUDIANTE
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Colección de operaciones del formulario de registro de nuevo estudiante.
 *
 * @namespace estudianteForm
 */
export const estudianteForm = {

    /** @type {Array<{id_producto:number, disponible:number}>} Stock cargado para la promoción activa. */
    _stock: [],

    /**
     * Limpia todos los campos del formulario y oculta la sección de productos.
     *
     * @returns {void}
     */
    limpiar() {
        ['efNombres','efApellidos','efNacDia','efNacMes','efNacAnio','efColor','efProfesion',
         'apNombres','apApellidos','apTelefono','apCorreo','apRelacion'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });

        this._stock = [];
        const section  = document.getElementById('efProductosSection');
        const loading  = document.getElementById('efProductosLoading');
        const items    = document.getElementById('efProductosItems');
        if (section)  section.style.display  = 'none';
        if (loading)  { loading.style.display = ''; items && (items.style.display = 'none'); }
        if (items)    items.innerHTML = '';
    },

    /**
     * Carga y renderiza los productos disponibles para la promoción en el modal.
     * Muestra stock disponible y auto-marca el producto si es el único de la promoción.
     *
     * @param {Array<{id_producto:number, nombre_producto:string, categoria:string,
     *                total:number, disponible:number}>} productos
     * @returns {void}
     */
    setStock(productos) {
        this._stock = productos;

        const section = document.getElementById('efProductosSection');
        const loading = document.getElementById('efProductosLoading');
        const items   = document.getElementById('efProductosItems');
        if (!section || !loading || !items) return;

        loading.style.display = 'none';
        items.innerHTML       = '';

        if (!productos.length) {
            section.style.display = 'none';
            return;
        }

        section.style.display = '';
        items.style.display   = '';

        const autoCheck = productos.length === 1;

        productos.forEach(p => {
            const disponible = p.disponible > 0;
            const checked    = autoCheck && disponible ? 'checked' : '';
            const disabled   = !disponible ? 'disabled' : '';
            const badge      = disponible
                ? `<span class="badge bg-success ms-1" style="font-size:.72rem;">${p.disponible} disponible${p.disponible !== 1 ? 's' : ''}</span>`
                : `<span class="badge bg-danger ms-1" style="font-size:.72rem;">Sin stock</span>`;

            items.insertAdjacentHTML('beforeend', `
                <div class="col-12 col-sm-6">
                    <div class="form-check" style="background:var(--bg-elevated);border:1px solid var(--border);
                                border-radius:8px;padding:.5rem .75rem .5rem 2.2rem;cursor:${disponible ? 'pointer' : 'not-allowed'};
                                opacity:${disponible ? '1' : '.55'};">
                        <input class="form-check-input ef-producto-chk" type="checkbox"
                               id="efProd_${p.id_producto}" value="${p.id_producto}"
                               ${checked} ${disabled}>
                        <label class="form-check-label" for="efProd_${p.id_producto}"
                               style="font-size:.84rem;cursor:${disponible ? 'pointer' : 'not-allowed'};">
                            ${p.nombre_producto}${badge}
                        </label>
                    </div>
                </div>`);
        });

        items.style.display = '';
    },

    /**
     * Devuelve los IDs de los productos seleccionados en el formulario.
     *
     * @returns {number[]}
     */
    _productosSeleccionados() {
        return [...document.querySelectorAll('.ef-producto-chk:checked')]
            .map(el => parseInt(el.value, 10));
    },

    /**
     * Lee los valores del formulario y construye el payload para `POST /api/estudiantes`.
     *
     * @param {number} idPromocion - ID de la promoción a la que se asocia el estudiante.
     * @returns {object}
     */
    datos(idPromocion) {
        return {
            id_promocion: idPromocion,
            productos: this._productosSeleccionados(),
            estudiante: {
                nombres:          (document.getElementById('efNombres').value   || '').trim().toLocaleUpperCase('es-PE'),
                apellidos:        (document.getElementById('efApellidos').value || '').trim().toLocaleUpperCase('es-PE') || null,
                fecha_nacimiento: _buildFecha('efNacAnio', 'efNacMes', 'efNacDia'),
                color_fav:        (document.getElementById('efColor').value     || '').trim() || null,
                profesion_futura: (document.getElementById('efProfesion').value || '').trim().toLocaleUpperCase('es-PE') || null,
            },
            apoderado: {
                nombres:       (document.getElementById('apNombres').value   || '').trim().toLocaleUpperCase('es-PE'),
                apellidos:     (document.getElementById('apApellidos').value || '').trim().toLocaleUpperCase('es-PE') || null,
                telefono:      document.getElementById('apTelefono').value.trim(),
                correo:        document.getElementById('apCorreo').value.trim() || null,
                tipo_relacion: document.getElementById('apRelacion').value,
            },
        };
    },

    /**
     * Valida los campos requeridos del formulario de estudiante.
     *
     * @returns {string|null} Mensaje de error o `null` si es válido.
     */
    validar() {
        const reqs = {
            efNombres:  'Los nombres del estudiante son obligatorios.',
            apNombres:  'Los nombres del apoderado son obligatorios.',
            apTelefono: 'El teléfono del apoderado es obligatorio.',
            apRelacion: 'La relación con el estudiante es obligatoria.',
        };
        for (const [id, msg] of Object.entries(reqs)) {
            if (!document.getElementById(id)?.value.trim()) return msg;
        }

        const err = _validarFecha('efNacAnio', 'efNacMes', 'efNacDia', 30);
        if (err) return err;

        const tel = document.getElementById('apTelefono').value.trim();
        if (!/^\d{9}$/.test(tel)) return 'El teléfono debe tener exactamente 9 dígitos.';

        // Verificar que productos seleccionados aún tienen stock (doble verificación cliente)
        const seleccionados = this._productosSeleccionados();
        const stockMap      = Object.fromEntries(this._stock.map(p => [p.id_producto, p.disponible]));
        for (const id of seleccionados) {
            if ((stockMap[id] ?? 0) <= 0) return 'Uno de los productos seleccionados ya no tiene stock disponible.';
        }

        return null;
    },
};
