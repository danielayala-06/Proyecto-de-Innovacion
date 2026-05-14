import { formatters }                                    from '../../utils/formatters.js';
import { TIPO_LABEL, TIPO_ICON, ESTADO_LABEL, ESTADO_CLASS } from './sesion.state.js';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function _estadoBadge(estado) {
    const cls   = ESTADO_CLASS[estado] ?? 'badge-pendiente';
    const label = ESTADO_LABEL[estado] ?? estado;
    return `<span class="${cls}">${label}</span>`;
}

function _tipoBadge(tipo) {
    const icon  = TIPO_ICON[tipo]  ?? 'bi-calendar';
    const label = TIPO_LABEL[tipo] ?? tipo;
    return `<span class="tipo-badge tipo-${tipo}"><i class="bi ${icon}"></i> ${label}</span>`;
}

// ─── Renders públicos ─────────────────────────────────────────────────────────

export const ui = {

    // ── Tabs de promociones ────────────────────────────────────────────────────
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

    // ── Panel de sesiones de una promoción ────────────────────────────────────
    renderSesiones(sesiones, limites, configTipos) {
        const container = document.getElementById('sesionesContainer');
        if (!container) return;

        const tiposDisponibles = [...new Set(configTipos.map(c => c.tipo_sesion))];

        const limiteBars = tiposDisponibles.map(tipo => {
            const lim  = limites[tipo] ?? { permitidas: 0, usadas: 0, puede_crear: false };
            const pct  = lim.permitidas ? Math.round((lim.usadas / lim.permitidas) * 100) : 0;
            const cls  = lim.puede_crear ? '' : 'full';
            return `
            <div class="limite-bar">
                <div class="limite-info">
                    ${_tipoBadge(tipo)}
                    <span class="limite-count">${lim.usadas} / ${lim.permitidas} sesiones</span>
                    ${lim.puede_crear
                        ? `<button class="btn-add-sesion btn-icon" data-tipo="${tipo}" title="Nueva sesión de ${TIPO_LABEL[tipo]}">
                               <i class="bi bi-plus-circle"></i>
                           </button>`
                        : `<span class="limite-agotado"><i class="bi bi-lock-fill"></i> Límite alcanzado</span>`}
                </div>
                <div class="limite-progress ${cls}">
                    <div class="limite-fill" style="width:${pct}%"></div>
                </div>
            </div>`;
        }).join('');

        if (!sesiones.length) {
            container.innerHTML = `
                <div class="limite-bars">${limiteBars}</div>
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
            <div class="limite-bars">${limiteBars}</div>
            <div class="sesiones-lista">${cards}</div>`;
    },

    // ── Panel de estudiantes de una promoción ─────────────────────────────────
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
            <div class="estudiante-row">
                <div class="estudiante-avatar">${e.nombres[0]}${e.apellidos[0]}</div>
                <div class="estudiante-info">
                    <div class="estudiante-nombre">${e.apellidos}, ${e.nombres}</div>
                    <div class="estudiante-apoderado">
                        <i class="bi bi-person-fill"></i>
                        ${e.apoderado_nombres} ${e.apoderado_apellidos ?? ''}
                        <span class="text-muted">(${e.tipo_relacion})</span>
                        · ${e.apoderado_telefono}
                    </div>
                </div>
                <button class="btn-icon danger" onclick="eliminarEstudiante(${e.id_estudiante})" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`).join('');

        container.innerHTML = header + `<div class="estudiantes-lista">${rows}</div>`;
    },

    // ── Panel de asistencia de una sesión (offcanvas) ─────────────────────────
    renderAsistencia(sesion, todosEstudiantes) {
        const container = document.getElementById('asistenciaContainer');
        if (!container) return;

        const enSesion   = new Set(sesion.asistencia.map(a => a.id_estudiante));
        const disponibles = todosEstudiantes.filter(e => !enSesion.has(e.id_estudiante));

        // Estudiantes ya en la sesión
        const enRows = sesion.asistencia.map(a => `
            <div class="asistencia-row">
                <div class="estudiante-avatar sm">${a.nombres[0]}${a.apellidos[0]}</div>
                <span class="flex-1">${a.apellidos}, ${a.nombres}</span>
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

        // Estudiantes disponibles para agregar
        const dispRows = disponibles.length ? `
            <div class="asistencia-section-title">Agregar a la sesión</div>
            ${disponibles.map(e => `
            <div class="asistencia-row dim">
                <div class="estudiante-avatar sm">${e.nombres[0]}${e.apellidos[0]}</div>
                <span class="flex-1">${e.apellidos}, ${e.nombres}</span>
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
