import { sesionApi }  from '../../api/sesion.api.js';
import { formatters } from '../../utils/formatters.js';
import { alerts }     from '../../utils/alerts.js';

// ── Estado ────────────────────────────────────────────────────────────────────
const state = {
  filas:     [],
  filtradas: [],
  pagina:    1,
  porPagina: 20,
};

// ── Badges ────────────────────────────────────────────────────────────────────
const ESTADO_BADGE = {
  pendiente:  ['badge-pendiente',  'Pendiente'],
  finalizado: ['badge-aprobada',   'Finalizado'],
  cancelado:  ['badge-rechazada',  'Cancelado'],
};

const TIPO_LABEL = {
  colegio:    'Colegio',
  exteriores: 'Exteriores',
  estudio:    'Estudio',
  otro:       'Otro',
};

function badgeEstado(estado) {
  const [cls, label] = ESTADO_BADGE[estado] ?? ['badge-inactivo', estado ?? '—'];
  return `<span class="${cls}">${label}</span>`;
}

// ── Estadísticas ──────────────────────────────────────────────────────────────
function renderStats(filas) {
  const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  set('statTotal',      filas.length);
  set('statPendientes', filas.filter(s => s.estado === 'pendiente').length);
  set('statFinalizadas',filas.filter(s => s.estado === 'finalizado').length);
  set('statCanceladas', filas.filter(s => s.estado === 'cancelado').length);
}

// ── Tabla ─────────────────────────────────────────────────────────────────────
function renderTabla(filas) {
  const tbody = document.getElementById('tablaBody');
  if (!tbody) return;

  if (!filas.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="con-empty">
          <i class="bi bi-camera" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
          No hay sesiones para mostrar.
        </td>
      </tr>`;
    return;
  }

  tbody.innerHTML = filas.map(s => {
    const colegio  = s.nombre_colegio ?? '—';
    const nivel    = [s.grado, s.seccion].filter(Boolean).join(' · ') || '—';
    const nEst     = s.num_estudiantes ?? '—';
    const contrato = s.id_contrato
      ? `<a class="con-cot-ref-small btn btn-small"
             href="${BASE_URL}contratos/${s.id_contrato}/sesiones"
             onclick="event.stopPropagation()">
           ${formatters.codigo(s.id_contrato)}
         </a>`
      : '<span style="color:var(--text-muted);">—</span>';
    const fecha    = s.fecha_hora_sesion
      ? formatters.fecha(s.fecha_hora_sesion.slice(0, 10)) +
        `<br><span style="font-size:.74rem;color:var(--text-muted);">${s.fecha_hora_sesion.slice(11, 16)}</span>`
      : '—';
    const tipo     = TIPO_LABEL[s.tipo] ?? s.tipo;
    const destino  = s.id_contrato
      ? `${BASE_URL}contratos/${s.id_contrato}/sesiones`
      : '#';

    return `
      <tr style="cursor:${s.id_contrato ? 'pointer' : 'default'};"
          onclick="${s.id_contrato ? `window.location.href='${destino}'` : ''}">
        <td>
          <span class="text-uppercase fw-semibold" style="font-size:.84rem;">${colegio}</span>
        </td>
        <td style="font-size:.82rem;color:var(--text-muted);">${nivel}</td>
        <td style="text-align:center;font-size:.85rem;">${nEst}</td>
        <td>${contrato}</td>
        <td style="font-size:.82rem;">${fecha}</td>
        <td style="font-size:.82rem;color:var(--text-muted);">${tipo}</td>
        <td>${badgeEstado(s.estado)}</td>
        <td style="text-align:center;" onclick="event.stopPropagation()">
          ${s.id_contrato
            ? `<a class="btn btn-sm btn-outline-primary"
                   href="${destino}"
                   title="Ver sesiones del contrato">
                 <i class="bi bi-camera"></i>
               </a>`
            : ''}
        </td>
      </tr>`;
  }).join('');
}

// ── Paginación ────────────────────────────────────────────────────────────────
function renderPaginacion(total, pagina, porPagina) {
  const totalPags = Math.ceil(total / porPagina) || 1;
  const info = document.getElementById('paginaInfo');
  const btns = document.getElementById('paginaBtns');
  if (!info || !btns) return;

  const desde = total ? (pagina - 1) * porPagina + 1 : 0;
  const hasta  = Math.min(pagina * porPagina, total);
  info.textContent = total ? `Mostrando ${desde}–${hasta} de ${total}` : 'Sin resultados';

  btns.innerHTML = Array.from({ length: totalPags }, (_, i) => i + 1)
    .map(i => `<button class="pag-btn${i === pagina ? ' active' : ''}"
                       onclick="irPagina(${i})">${i}</button>`)
    .join('');
}

function renderPagina() {
  const { filtradas, pagina, porPagina } = state;
  const desde = (pagina - 1) * porPagina;
  renderTabla(filtradas.slice(desde, desde + porPagina));
  renderPaginacion(filtradas.length, pagina, porPagina);
}

// ── Filtrado ──────────────────────────────────────────────────────────────────
function filtrar() {
  const search = (document.getElementById('searchInput')?.value  || '').toLowerCase().trim();
  const estado = (document.getElementById('filterEstado')?.value || '').toLowerCase().trim();
  const tipo   = (document.getElementById('filterTipo')?.value   || '').toLowerCase().trim();

  state.filtradas = state.filas.filter(s => {
    const okSearch = !search
      || (s.nombre_colegio ?? '').toLowerCase().includes(search)
      || String(s.id_contrato ?? '').includes(search)
      || (s.nombre_promocion ?? '').toLowerCase().includes(search);
    const okEstado = !estado || s.estado === estado;
    const okTipo   = !tipo   || s.tipo   === tipo;
    return okSearch && okEstado && okTipo;
  });

  state.pagina = 1;
  renderPagina();
}

window.irPagina = function (n) {
  state.pagina = n;
  renderPagina();
};

// ── Carga inicial ─────────────────────────────────────────────────────────────
function renderLoading() {
  const tbody = document.getElementById('tablaBody');
  if (!tbody) return;
  const fila = `<td><div class="placeholder-glow"><span class="placeholder col-8"></span></div></td>`;
  tbody.innerHTML = Array.from({ length: 5 }, () =>
    `<tr style="opacity:.5;">${fila.repeat(8)}</tr>`
  ).join('');
}

async function init() {
  renderLoading();
  try {
    const res = await sesionApi.listar();
    state.filas = res.data ?? [];
    renderStats(state.filas);
    state.filtradas = [...state.filas];
    renderPagina();
  } catch (e) {
    alerts.error(e.message || 'No se pudieron cargar las sesiones.');
    const tbody = document.getElementById('tablaBody');
    if (tbody) tbody.innerHTML = `
      <tr>
        <td colspan="8" class="text-center py-4" style="color:var(--red-text);font-size:.85rem;">
          <i class="bi bi-exclamation-circle me-1"></i>Error al cargar los datos.
        </td>
      </tr>`;
  }
}

document.getElementById('searchInput') ?.addEventListener('input',  filtrar);
document.getElementById('filterEstado')?.addEventListener('change', filtrar);
document.getElementById('filterTipo')  ?.addEventListener('change', filtrar);

init();
