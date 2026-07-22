import { formatters }                                        from '../../utils/formatters.js';
import { TIPO_LABEL, TIPO_ICON, ESTADO_LABEL, ESTADO_CLASS } from './sesion.state.js';

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS PRIVADOS
// ─────────────────────────────────────────────────────────────────────────────

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

function _slotVacio(num) {
    return `
        <div class="sesion-slot empty">
            <span class="slot-num-lbl">Sesión ${num}</span>
            <i class="bi bi-calendar-x" style="font-size:1.5rem;color:var(--border);"></i>
            <span class="slot-empty-hint">Sin programar</span>
        </div>`;
}

function _slotLleno(s, num) {
    const [fecha, horaFull] = s.fecha_hora_sesion.split(' ');
    const hora = horaFull?.slice(0, 5) ?? '';
    return `
        <div class="sesion-slot filled">
            <div class="slot-top-row">
                <span class="slot-num-lbl">Sesión ${num}</span>
                <div class="slot-actions-row">
                    ${s.estado !== 'finalizado' && s.estado !== 'cancelado' ? `
                    <button class="btn-icon" onclick="abrirEditarSesion(${s.id_sesion})" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </button>` : ''}
                    ${s.estado === 'pendiente' ? `
                    <button class="btn-icon success" onclick="cambiarEstadoSesion(${s.id_sesion},'finalizado')" title="Finalizar">
                        <i class="bi bi-check-circle"></i>
                    </button>
                    <button class="btn-icon danger" onclick="cambiarEstadoSesion(${s.id_sesion},'cancelado')" title="Cancelar">
                        <i class="bi bi-x-circle"></i>
                    </button>` : ''}
                </div>
            </div>
            ${_tipoBadge(s.tipo)}
            <div class="slot-fecha-hora">
                <i class="bi bi-calendar3"></i>
                ${formatters.fecha(fecha)}
                <strong>${hora}</strong>
            </div>
            ${_estadoBadge(s.estado)}
            ${s.observaciones ? `<div class="slot-obs">${s.observaciones}</div>` : ''}
        </div>`;
}

function _tablaAsistencia(activas, estudiantes, asistencias, promo) {
    const sesionCols = [0, 1, 2].map(i => {
        const s = activas[i];
        if (!s) {
            return `<th class="asis-th-slot">
                <div class="asis-slot-card empty">
                    <span class="asis-slot-empty-hint">Sin programar</span>
                </div>
            </th>`;
        }
        const [fecha, horaFull] = s.fecha_hora_sesion.split(' ');
        const hora = horaFull?.slice(0, 5) ?? '';
        const h    = parseInt(hora, 10);
        const min  = hora.slice(3, 5);
        const ampm = h >= 12 ? 'p.m.' : 'a.m.';
        const h12  = h % 12 || 12;
        return `<th class="asis-th-slot asis-th-clickable" onclick="verDetalleSesion(${s.id_sesion})" title="Ver detalles de la sesión">
            <div class="asis-slot-card filled" style="text-align:center;">
                <div class="asis-slot-fecha" style="justify-content:center;">
                    <i class="bi bi-calendar3"></i>
                    ${formatters.fecha(fecha)}
                    <strong>${h12}:${min} ${ampm}</strong>
                </div>
                ${_estadoBadge(s.estado)}
            </div>
        </th>`;
    }).join('');

    let asistieron = 0, faltaron = 0, sinMarcar = 0;

    const bodyRows = estudiantes.map((e, idx) => {
        const celdas = [0, 1, 2].map(i => {
            const s = activas[i];
            if (!s) return `<td class="asis-td disabled"></td>`;
            const asisLista = asistencias[s.id_sesion] ?? [];
            const entrada   = asisLista.find(a => a.id_estudiante === e.id_estudiante);
            const val       = entrada != null ? entrada.asistio : null;
            if (val === 1)      asistieron++;
            else if (val === 0) faltaron++;
            else                sinMarcar++;
            const valJs   = val === null ? 'null' : val;
            const cellHtml = val === 1
                ? `<div class="asis-cell asistio"><i class="bi bi-x-lg"></i></div>`
                : val === 0
                    ? `<div class="asis-cell falto"><i class="bi bi-dash-lg"></i></div>`
                    : `<div class="asis-cell vacio"></div>`;
            return `<td class="asis-td" onclick="toggleAsistencia(${s.id_sesion},${e.id_estudiante},${valJs})">${cellHtml}</td>`;
        }).join('');

        const nombre = e.apellidos ? `${e.apellidos}, ${e.nombres}` : e.nombres;
        return `<tr>
            <td class="asis-td-num asis-td-click" onclick="verDetalleEstudiante(${e.id_estudiante})">${String(idx + 1).padStart(2, '0')}</td>
            <td class="asis-td-nombre asis-td-click" onclick="verDetalleEstudiante(${e.id_estudiante})">${nombre}</td>
            ${celdas}
            <td class="asis-td-obs"><input type="text" class="asis-obs-input" placeholder="Opcional"></td>
        </tr>`;
    }).join('');

    const promoBar = promo ? `
        <div class="asis-promo-bar">
            <i class="bi bi-mortarboard"></i>
            <span class="asis-promo-nombre">${promo.nombre_colegio ?? promo.nombre}</span>
            <span class="asis-promo-sep">·</span>
            <span class="asis-promo-meta">${promo.grado}${promo.seccion ? ' ' + promo.seccion : ''} — ${promo.nombre}</span>
        </div>` : '';

    const imprimirBtn = promo ? `
        <button class="btn btn-outline-primary btn-sm" onclick="imprimirListaPromocion(${promo.id_promocion})" style="white-space:nowrap;">
            <i class="bi bi-printer"></i> Imprimir lista
        </button>` : '';

    return `
        <div class="asis-section">
            ${promoBar}
            <div class="asis-hdr" style="gap:.75rem;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <div class="asis-hdr-title">
                        <i class="bi bi-calendar3-event"></i> Control de asistencia
                    </div>
                    <div class="asis-hdr-meta"><i class="bi bi-calendar-check me-1"></i>Total sesiones: ${activas.length}</div>
                </div>
                ${imprimirBtn}
            </div>
            <p class="asis-hint">Haz clic en una celda para marcar · <strong>X</strong> asistió · <strong>—</strong> faltó · vacío sin marcar</p>
            <div class="asis-table-wrap">
                <table class="asis-table">
                    <thead><tr>
                        <th class="asis-th-num">N°</th>
                        <th class="asis-th-nombre">Nombre del alumno</th>
                        ${sesionCols}
                        <th class="asis-th-obs">Observaciones</th>
                    </tr></thead>
                    <tbody>${bodyRows || `<tr><td class="asis-td-empty" colspan="6">Sin estudiantes registrados en esta promoción.</td></tr>`}</tbody>
                </table>
            </div>
            <div class="asis-footer">
                <div class="asis-stat">
                    <i class="bi bi-x-circle-fill" style="color:var(--accent);font-size:1rem;"></i>
                    <span>Asistieron</span>
                    <strong class="asis-stat-val">${asistieron}</strong>
                </div>
                <div class="asis-stat">
                    <i class="bi bi-dash-circle" style="color:var(--red-text);font-size:1rem;"></i>
                    <span>Faltaron</span>
                    <strong class="asis-stat-val">${faltaron}</strong>
                </div>
                <div class="asis-stat">
                    <i class="bi bi-circle" style="color:var(--text-muted);font-size:1rem;"></i>
                    <span>Sin marcar</span>
                    <strong class="asis-stat-val">${sinMarcar}</strong>
                </div>
                <div class="asis-stat-total">
                    <span>Total de alumnos</span>
                    <strong class="asis-stat-val">${estudiantes.length}</strong>
                </div>
            </div>
        </div>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// UI PÚBLICA
// ─────────────────────────────────────────────────────────────────────────────

export const ui = {

    renderTabs(promociones, activeId) {
        const nav = document.getElementById('promocionesTabs');
        if (!nav) return;
        if (promociones.length <= 1) { nav.style.display = 'none'; return; }
        nav.style.display = '';
        nav.className = 'promo-tabs-bar';
        nav.innerHTML = promociones.map(p => `
            <button class="promo-tab${p.id_promocion === activeId ? ' active' : ''}"
                    data-id="${p.id_promocion}">
                <i class="bi bi-mortarboard"></i>
                <span>${p.nombre_colegio ?? p.nombre}</span>
                <small>${p.grado}${p.seccion ? ' ' + p.seccion : ''}</small>
            </button>`).join('');
    },

    renderSesiones(sesiones, estudiantes, asistencias, promo) {
        const container = document.getElementById('sesionesContainer');
        if (!container) return;

        const activas = sesiones.filter(s => s.estado !== 'cancelado');

        container.innerHTML = (activas.length > 0 || estudiantes.length > 0)
            ? _tablaAsistencia(activas, estudiantes, asistencias, promo)
            : `<div class="asis-empty-hint">
                <i class="bi bi-camera me-1"></i>
                Aún no hay sesiones agendadas.
                <button class="btn btn-primary btn-sm ms-2" onclick="abrirNuevaSesion()">
                    <i class="bi bi-camera me-1"></i>Agendar primera sesión
                </button>
               </div>`;
    },

    renderEstudiantes(estudiantes, idPromocion) {
        const container = document.getElementById('estudiantesContainer');
        if (!container) return;
        const tieneEstudiantes = estudiantes.length > 0;
        container.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <span style="font-size:.78rem;color:var(--text-muted);">
                    <i class="bi bi-people me-1"></i>
                    ${estudiantes.length} estudiante${estudiantes.length !== 1 ? 's' : ''}
                </span>
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;">
                    <button class="btn btn-outline-secondary btn-sm" onclick="abrirImportarCsv(${idPromocion})"
                            title="Importar desde CSV">
                        <i class="bi bi-upload me-1"></i>Importar
                    </button>
                    ${tieneEstudiantes ? `
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportarCsvEstudiantes(${idPromocion})"
                            title="Exportar a CSV">
                        <i class="bi bi-download me-1"></i>Exportar
                    </button>` : ''}
                    <button class="btn btn-nuevo-paquete" onclick="abrirNuevoEstudiante(${idPromocion})"
                            style="padding:5px 12px;font-size:.76rem;">
                        <i class="bi bi-person-plus"></i> Agregar
                    </button>
                </div>
            </div>`;
    },
};
