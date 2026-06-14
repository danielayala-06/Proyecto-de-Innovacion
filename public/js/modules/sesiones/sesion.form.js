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
 *  `efNombres`, `efApellidos`, `efNacimiento`, `efColor`, `efProfesion`,
 *  `apNombres`, `apApellidos`, `apTelefono`, `apDocTipo`, `apDocNum`,
 *  `apCorreo`, `apRelacion`
 */

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
        document.getElementById('sfHora').value          = '09:00';
        document.getElementById('sfObservaciones').value = '';

        // Restricciones de fecha: hoy → +10 meses
        const hoy = new Date();
        const max = new Date(hoy);
        max.setMonth(max.getMonth() + 10);
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
        if (dt > max) return 'La sesión no puede programarse con más de 10 meses de anticipación.';

        const h = dt.getHours();
        if (h < 9 || h >= 20) return 'El horario debe ser entre las 9:00 a.m. y las 8:00 p.m.';

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

    /**
     * Limpia todos los campos del formulario de estudiante y apoderado.
     *
     * @returns {void}
     */
    limpiar() {
        ['efNombreCompleto','efNacimiento','efColor','efProfesion',
         'apNombreCompleto','apTelefono','apCorreo','apRelacion'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });

        // Fecha de nacimiento: no futura, no más de 120 años atrás
        const hoy = new Date();
        const min = new Date(hoy.getFullYear() - 30, hoy.getMonth(), hoy.getDate());
        const toISO = d => d.toISOString().split('T')[0];
        const ef = document.getElementById('efNacimiento');
        if (ef) { ef.min = toISO(min); ef.max = toISO(hoy); }
    },

    /**
     * Lee los valores del formulario y construye el payload para `POST /api/estudiantes`.
     *
     * @param {number} idPromocion - ID de la promoción a la que se asocia el estudiante.
     * @returns {{
     *   id_promocion: number,
     *   estudiante: {
     *     nombres:          string,
     *     apellidos:        string,
     *     fecha_nacimiento: string|null,
     *     color_fav:        string|null,
     *     profesion_futura: string|null
     *   },
     *   apoderado: {
     *     nombres:          string,
     *     apellidos:        string|null,
     *     telefono:         string,
     *     tipo_documento:   string,
     *     numero_documento: string,
     *     correo:           string|null,
     *     tipo_relacion:    string
     *   }
     * }}
     */
    datos(idPromocion) {
        return {
            id_promocion: idPromocion,
            estudiante: {
                nombres:          document.getElementById('efNombreCompleto').value.trim(),
                apellidos:        null,
                fecha_nacimiento: document.getElementById('efNacimiento').value || null,
                color_fav:        document.getElementById('efColor').value.trim()     || null,
                profesion_futura: document.getElementById('efProfesion').value.trim() || null,
            },
            apoderado: {
                nombres:       document.getElementById('apNombreCompleto').value.trim(),
                apellidos:     null,
                telefono:      document.getElementById('apTelefono').value.trim(),
                correo:        document.getElementById('apCorreo').value.trim() || null,
                tipo_relacion: document.getElementById('apRelacion').value,
            },
        };
    },

    /**
     * Valida los campos requeridos del formulario de estudiante.
     * Verifica que el teléfono del apoderado tenga exactamente 9 dígitos.
     *
     * @returns {string|null} Mensaje de error si falla la validación, o `null` si es válido.
     */
    validar() {
        const reqs = {
            efNombreCompleto: 'El nombre completo del estudiante es obligatorio.',
            apNombreCompleto: 'El nombre completo del apoderado es obligatorio.',
            apTelefono:       'El teléfono del apoderado es obligatorio.',
            apRelacion:       'La relación con el estudiante es obligatoria.',
        };
        for (const [id, msg] of Object.entries(reqs)) {
            if (!document.getElementById(id)?.value.trim()) return msg;
        }

        const nacimiento = document.getElementById('efNacimiento').value;
        if (nacimiento) {
            const fn  = new Date(nacimiento + 'T00:00:00');
            const hoy = new Date(); hoy.setHours(0, 0, 0, 0);
            const min = new Date(hoy.getFullYear() - 30, hoy.getMonth(), hoy.getDate());
            if (fn > hoy) return 'La fecha de nacimiento no puede ser en el futuro.';
            if (fn < min) return 'El estudiante no puede tener más de 30 años.';
        }

        const tel = document.getElementById('apTelefono').value.trim();
        if (!/^\d{9}$/.test(tel)) return 'El teléfono debe tener exactamente 9 dígitos.';
        return null;
    },
};
