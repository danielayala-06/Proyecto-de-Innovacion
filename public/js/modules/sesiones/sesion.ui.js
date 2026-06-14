/**
 * @file    sesion.ui.js
 * @module  modules/sesiones/ui
 *
 * Capa de renderizado DOM del módulo de sesiones fotográficas.
 * Todas las funciones manipulan el DOM directamente; ninguna realiza
 * llamadas a la API ni modifica el estado global.
 *
 * Elementos DOM requeridos (IDs):
 *  - `promocionesTabs`    : nav de pestañas de promociones.
 *  - `sesionesContainer`  : panel que muestra las sesiones y barras de límite.
 *  - `estudiantesContainer`: panel lateral con la lista de estudiantes.
 *  - `asistenciaContainer`: contenedor del offcanvas de asistencia.
 */

import { formatters }                                        from '../../utils/formatters.js';
import { TIPO_LABEL, TIPO_ICON, ESTADO_LABEL, ESTADO_CLASS } from './sesion.state.js';

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS PRIVADOS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Genera el HTML del badge de estado de una sesión.
 *
 * @param {string} estado - Estado de la sesión (`'pendiente'`, `'finalizado'`, `'cancelado'`).
 * @returns {string} HTML del `<span>` con la clase CSS correspondiente.
 */
function _estadoBadge(estado) {
    const cls   = ESTADO_CLASS[estado] ?? 'badge-pendiente';
    const label = ESTADO_LABEL[estado] ?? estado;
    return `<span class="${cls}">${label}</span>`;
}

/**
 * Genera el HTML del badge de tipo de sesión con icono Bootstrap Icons.
 *
 * @param {string} tipo - Tipo de sesión (`'exteriores'`, `'colegio'`, `'estudio'`, `'otro'`).
 * @returns {string} HTML del `<span class="tipo-badge">` con icono y etiqueta.
 */
function _tipoBadge(tipo) {
    const icon  = TIPO_ICON[tipo]  ?? 'bi-calendar';
    const label = TIPO_LABEL[tipo] ?? tipo;
    return `<span class="tipo-badge tipo-${tipo}"><i class="bi ${icon}"></i> ${label}</span>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// UI PÚBLICA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Colección de funciones de renderizado del módulo de sesiones.
 *
 * @namespace ui
 */
export const ui = {

    // ── Pestañas de promociones ───────────────────────────────────────────────

    /**
     * Renderiza las pestañas de promociones en `#promocionesTabs`.
     * La pestaña activa recibe la clase `active`.
     *
     * @param {Array<Object>} promociones        - Lista de promociones del contrato.
     * @param {number|null}   activeId           - ID de la promoción actualmente seleccionada.
     * @returns {void}
     */
    renderTabs(promociones, activeId) {
        const nav = document.getElementById('promocionesTabs');
        if (!nav) return;
        nav.innerHTML = promociones.map(p => `
            <button class="promo-tab${p.id_promocion === activeId ? ' active' : ''}"
                    data-id="${p.id_promocion}">
                <i class="bi bi-mortarboard"></i>
                <span>${p.nombre}</span>
                <small>${p.grado}${p.seccion ? ' ' + p.seccion : ''}</small>
            </button>`).join('');
    },

    // ── Panel de sesiones ─────────────────────────────────────────────────────

    /**
     * Renderiza el panel de sesiones de una promoción en `#sesionesContainer`.
     *
     * Incluye:
     *  - Barras de progreso de uso de límites por tipo de sesión.
     *  - Botón de "Nueva sesión" inline en cada barra (si `puede_crear`).
     *  - Tarjetas `.sesion-card` con tipo, estado, fecha, hora, observaciones
     *    y botones de acción contextuales (editar, asistencia, finalizar, cancelar).
     *
     * @param {Array<Object>} sesiones     - Sesiones de la promoción activa.
     * @param {Object}        limites      - Mapa `tipo → {permitidas, usadas, puede_crear}`.
     * @param {Array<Object>} configTipos  - Configuración de tipos desde `paquetes_sesiones`.
     * @returns {void}
     */
    renderSesiones(sesiones, puedeAgregar) {
        const container = document.getElementById('sesionesContainer');
        if (!container) return;

        const totalActivas = sesiones.filter(s => s.estado !== 'cancelado').length;
        const pct = Math.round((totalActivas / 3) * 100);

        const header = `
            <div class="limite-bar">
                <div class="limite-info">
                    <span class="limite-count">
                        <i class="bi bi-camera"></i>
                        Sesiones activas: <strong>${totalActivas} / 3</strong>
                    </span>
                    ${puedeAgregar
                        ? `<button class="btn-nuevo-paquete" onclick="abrirNuevaSesion()" style="margin-left:auto;padding:5px 14px;font-size:.78rem;">
                               <i class="bi bi-plus-circle"></i> Nueva sesión
                           </button>`
                        : `<span class="limite-agotado" style="margin-left:auto;"><i class="bi bi-lock-fill"></i> Límite alcanzado</span>`}
                </div>
                <div class="limite-progress${totalActivas >= 3 ? ' full' : ''}">
                    <div class="limite-fill" style="width:${pct}%"></div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;padding:6px 0 4px;">
                <span style="font-size:.75rem;color:var(--text-muted);">Ordenar por</span>
                <select id="sesionSortCampo"
                        onchange="ordenarSesiones()"
                        style="font-size:.78rem;padding:3px 8px;border-radius:6px;
                               border:1px solid var(--border-color);background:var(--bg-surface);
                               color:var(--text-primary);cursor:pointer;">
                    <option value="sesion">Fecha sesión</option>
                    <option value="creacion">Fecha creación</option>
                </select>
                <button id="sesionSortAsc" onclick="ordenarSesiones('asc')" title="Ascendente"
                        style="padding:3px 7px;border-radius:6px;border:1px solid var(--border-color);
                               background:var(--bg-surface);color:var(--text-primary);cursor:pointer;font-size:.8rem;line-height:1;">
                    <i class="bi bi-arrow-up"></i>
                </button>
                <button id="sesionSortDesc" onclick="ordenarSesiones('desc')" title="Descendente"
                        style="padding:3px 7px;border-radius:6px;border:1px solid var(--border-color);
                               background:var(--bg-surface);color:var(--text-primary);cursor:pointer;font-size:.8rem;line-height:1;">
                    <i class="bi bi-arrow-down"></i>
                </button>
            </div>`;

        if (!sesiones.length) {
            container.innerHTML = `
                <div class="limite-bars">${header}</div>
                <div class="empty-state" style="padding:2rem;">
                    <i class="bi bi-calendar-x"></i>
                    No hay sesiones registradas para esta promoción.
                </div>`;
            return;
        }

        const cards = sesiones.map(s => `
            <div class="sesion-card estado-${s.estado}">
                <div class="sesion-card-header">
                    <div class="d-flex align-items-center gap-2">
                        ${_tipoBadge(s.tipo)}
                        ${_estadoBadge(s.estado)}
                    </div>
                    <div class="sesion-actions">
                        ${s.estado !== 'finalizado' ? `
                        <button class="btn-icon" onclick="abrirEditarSesion(${s.id_sesion})" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>` : ''}
                        <button class="btn-icon" onclick="abrirAsistencia(${s.id_sesion})" title="Ver asistencia">
                            <i class="bi bi-people"></i>
                        </button>
                        ${s.estado === 'pendiente' ? `
                        <button class="btn-icon success" onclick="cambiarEstadoSesion(${s.id_sesion},'finalizado')" title="Marcar como finalizada">
                            <i class="bi bi-check-circle"></i>
                        </button>
                        <button class="btn-icon danger" onclick="cambiarEstadoSesion(${s.id_sesion},'cancelado')" title="Cancelar sesión">
                            <i class="bi bi-x-circle"></i>
                        </button>` : ''}
                    </div>
                </div>
                <div class="sesion-card-body">
                    <div class="sesion-fecha">
                        <i class="bi bi-calendar3"></i>
                        ${formatters.fecha(s.fecha_hora_sesion.split(' ')[0])}
                        <span class="sesion-hora">${s.fecha_hora_sesion.slice(11, 16)}</span>
                    </div>
                    ${s.observaciones ? `<div class="sesion-obs">${s.observaciones}</div>` : ''}
                </div>
            </div>`).join('');

        container.innerHTML = `
            <div class="limite-bars">${header}</div>
            <div class="sesiones-lista">${cards}</div>`;
    },

    // ── Panel de estudiantes ──────────────────────────────────────────────────

    /**
     * Renderiza el panel de estudiantes de la promoción activa en `#estudiantesContainer`.
     *
     * Muestra el contador de estudiantes, el botón de agregar y la lista de filas
     * con avatar (iniciales), nombre, datos del apoderado y botón de eliminar.
     *
     * @param {Array<Object>} estudiantes - Estudiantes de la promoción.
     * @param {number}        idPromocion - ID de la promoción activa (para el botón de agregar).
     * @returns {void}
     */
    renderEstudiantes(estudiantes, idPromocion) {
        const container = document.getElementById('estudiantesContainer');
        if (!container) return;

        const header = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="section-label" style="font-size:.75rem;">
                    ${estudiantes.length} estudiante${estudiantes.length !== 1 ? 's' : ''}
                </span>
                <button class="btn-nuevo-paquete btn-sm" onclick="abrirNuevoEstudiante(${idPromocion})">
                    <i class="bi bi-person-plus"></i> Agregar
                </button>
            </div>`;

        if (!estudiantes.length) {
            container.innerHTML = header + `
                <div class="empty-state" style="padding:1.5rem;">
                    <i class="bi bi-people"></i>
                    No hay estudiantes en esta promoción.
                </div>`;
            return;
        }

        const rows = estudiantes.map(e => `
            <div class="estudiante-row" style="cursor:pointer;" onclick="verDetalleEstudiante(${e.id_estudiante})">
                <div class="estudiante-avatar">${(e.nombres ?? '?')[0]}${e.apellidos ? e.apellidos[0] : (e.nombres?.[1] ?? '')}</div>
                <div class="estudiante-info">
                    <div class="estudiante-nombre">${e.apellidos ? `${e.apellidos}, ` : ''}${e.nombres}</div>
                    <div class="estudiante-apoderado">
                        <i class="bi bi-person-fill"></i>
                        ${e.apoderado_nombres} ${e.apoderado_apellidos ?? ''}
                        <span class="text-muted">(${e.tipo_relacion})</span>
                        · ${e.apoderado_telefono}
                    </div>
                </div>
                <button class="btn-icon danger" onclick="event.stopPropagation();eliminarEstudiante(${e.id_estudiante})" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`).join('');

        container.innerHTML = header + `<div class="estudiantes-lista">${rows}</div>`;
    },

    // ── Offcanvas de asistencia ───────────────────────────────────────────────

    /**
     * Renderiza el contenido del offcanvas de asistencia en `#asistenciaContainer`.
     *
     * Muestra dos secciones:
     *  1. Estudiantes ya en la sesión: con botones de "Asistió" / "Ausente" y "Quitar".
     *  2. Estudiantes disponibles para agregar a la sesión.
     *
     * @param {Object}        sesion           - Sesión activa con campo `asistencia[]`.
     * @param {number}        sesion.id_sesion
     * @param {Array<Object>} sesion.asistencia - Estudiantes ya vinculados con campo `asistio`.
     * @param {Array<Object>} todosEstudiantes  - Lista completa de estudiantes de la promoción.
     * @returns {void}
     */
    renderAsistencia(sesion, todosEstudiantes) {
        const container = document.getElementById('asistenciaContainer');
        if (!container) return;

        const enSesion    = new Set(sesion.asistencia.map(a => a.id_estudiante));
        const disponibles = todosEstudiantes.filter(e => !enSesion.has(e.id_estudiante));

        const enRows = sesion.asistencia.map(a => `
            <div class="asistencia-row">
                <div class="estudiante-avatar sm">${(a.nombres ?? '?')[0]}${a.apellidos ? a.apellidos[0] : (a.nombres?.[1] ?? '')}</div>
                <span class="flex-1">${a.apellidos ? `${a.apellidos}, ` : ''}${a.nombres}</span>
                <div class="asistencia-btns">
                    <button class="btn-asistencia ${a.asistio === 1 ? 'active-ok' : ''}"
                            onclick="marcarAsistencia(${sesion.id_sesion},${a.id_estudiante},1)" title="Asistió">
                        <i class="bi bi-check-lg"></i>
                    </button>
                    <button class="btn-asistencia ${a.asistio === 0 ? 'active-no' : ''}"
                            onclick="marcarAsistencia(${sesion.id_sesion},${a.id_estudiante},0)" title="Ausente">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <button class="btn-icon danger sm" onclick="quitarDeAsistencia(${sesion.id_sesion},${a.id_estudiante})" title="Quitar">
                    <i class="bi bi-dash-circle"></i>
                </button>
            </div>`).join('');

        const dispRows = disponibles.length ? `
            <div class="asistencia-section-title">Agregar a la sesión</div>
            ${disponibles.map(e => `
            <div class="asistencia-row dim">
                <div class="estudiante-avatar sm">${(e.nombres ?? '?')[0]}${e.apellidos ? e.apellidos[0] : (e.nombres?.[1] ?? '')}</div>
                <span class="flex-1">${e.apellidos ? `${e.apellidos}, ` : ''}${e.nombres}</span>
                <button class="btn-icon success sm" onclick="agregarAAsistencia(${sesion.id_sesion},${e.id_estudiante})" title="Agregar">
                    <i class="bi bi-plus-circle"></i>
                </button>
            </div>`).join('')}` : '';

        container.innerHTML = `
            <div class="asistencia-section-title">
                En esta sesión (${sesion.asistencia.length})
            </div>
            ${enRows || '<p class="text-muted small">Ningún estudiante agregado.</p>'}
            ${dispRows}`;
    },
};
