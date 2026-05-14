import { sesionApi }                                      from '../../api/sesion.api.js';
import { formatters }                                      from '../../utils/formatters.js';
import { TIPO_LABEL, TIPO_ICON, ESTADO_LABEL, ESTADO_CLASS } from './sesion.state.js';

let _sesiones = [];
let _filtrado = [];

// ─── Render ───────────────────────────────────────────────────────────────────

function _tipoBadge(tipo) {
    const icon  = TIPO_ICON[tipo]  ?? 'bi-calendar';
    const label = TIPO_LABEL[tipo] ?? tipo;
    return `<span class="tipo-badge tipo-${tipo}"><i class="bi ${icon}"></i> ${label}</span>`;
}

function _estadoBadge(estado) {
    const cls   = ESTADO_CLASS[estado] ?? 'badge-pendiente';
    const label = ESTADO_LABEL[estado] ?? estado;
    return `<span class="${cls}">${label}</span>`;
}

function _renderStats() {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('statTotal',      _filtrado.length);
    set('statPendientes', _filtrado.filter(s => s.estado === 'pendiente').length);
    set('statFinalizadas', _filtrado.filter(s => s.estado === 'finalizado').length);
    set('statCanceladas', _filtrado.filter(s => s.estado === 'cancelado').length);
}

function _render() {
    const tbody = document.getElementById('tablaBody');
    const info  = document.getElementById('paginaInfo');
    if (!tbody) return;

    if (!_filtrado.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="con-empty">
                    <i class="bi bi-camera" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                    No hay sesiones para mostrar.
                </td>
            </tr>`;
        if (info) info.textContent = 'Sin resultados';
        return;
    }

    tbody.innerHTML = _filtrado.map(s => {
        const fecha = s.fecha_hora_sesion
            ? formatters.fecha(s.fecha_hora_sesion.slice(0, 10)) +
              ' ' + s.fecha_hora_sesion.slice(11, 16)
            : '—';

        const verLink = s.id_contrato
            ? `<a class="btn btn-sm btn-outline-secondary" title="Ver sesiones del contrato"
                  href="contratos/${s.id_contrato}/sesiones">
                 <i class="bi bi-arrow-right-circle"></i>
               </a>`
            : `<button class="btn btn-sm btn-outline-secondary" disabled title="Sin contrato vinculado">
                 <i class="bi bi-arrow-right-circle"></i>
               </button>`;

        return `
            <tr>
                <td>${_tipoBadge(s.tipo)}</td>
                <td>
                    <span style="font-size:.85rem;">${s.nombre_promocion ?? '—'}</span>
                    ${s.grado ? `<br><small style="color:var(--text-muted);">${s.grado}</small>` : ''}
                </td>
                <td style="font-size:.84rem;">${s.nombre_colegio ?? '—'}</td>
                <td style="font-size:.83rem;">${fecha}</td>
                <td>${_estadoBadge(s.estado)}</td>
                <td style="text-align:center;">${verLink}</td>
            </tr>`;
    }).join('');

    if (info) info.textContent = `${_filtrado.length} sesión${_filtrado.length !== 1 ? 'es' : ''}`;
}

// ─── Filtros ──────────────────────────────────────────────────────────────────

function _aplicarFiltros() {
    const q      = document.getElementById('searchInput')?.value.toLowerCase().trim() ?? '';
    const estado = document.getElementById('filterEstado')?.value ?? '';
    const tipo   = document.getElementById('filterTipo')?.value   ?? '';

    _filtrado = _sesiones.filter(s => {
        if (estado && s.estado !== estado) return false;
        if (tipo   && s.tipo   !== tipo)   return false;
        if (q) {
            const haystack = [s.nombre_promocion, s.nombre_colegio, s.grado]
                .filter(Boolean).join(' ').toLowerCase();
            if (!haystack.includes(q)) return false;
        }
        return true;
    });

    _render();
    _renderStats();
}

// ─── Error / loading helpers ──────────────────────────────────────────────────

function _renderLoading() {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    const fila = `<td><div class="placeholder-glow"><span class="placeholder col-8"></span></div></td>`;
    tbody.innerHTML = Array.from({ length: 5 }, () =>
        `<tr style="opacity:.5;">${fila.repeat(6)}</tr>`
    ).join('');
}

function _renderError() {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4" style="color:var(--red-text);font-size:.85rem;">
                <i class="bi bi-exclamation-circle me-1"></i>Error al cargar las sesiones.
            </td>
        </tr>`;
}

// ─── Init ─────────────────────────────────────────────────────────────────────

async function init() {
    _renderLoading();

    try {
        const res = await sesionApi.listar();
        _sesiones = res.data ?? [];
    } catch {
        _renderError();
        return;
    }

    _aplicarFiltros();

    document.getElementById('searchInput')?.addEventListener('input', _aplicarFiltros);
    document.getElementById('filterEstado')?.addEventListener('change', _aplicarFiltros);
    document.getElementById('filterTipo')?.addEventListener('change', _aplicarFiltros);
}

init();
