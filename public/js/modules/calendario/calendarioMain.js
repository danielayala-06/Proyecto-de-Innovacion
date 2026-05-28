import { sesionApi } from '../../api/sesion.api.js';

const DIAS  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
const MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
               'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
const TIPO_LABEL = { colegio:'Colegio', exteriores:'Exteriores', estudio:'Estudio', otro:'Otro' };

const state = {
  sesiones:        [],
  año:             new Date().getFullYear(),
  mes:             new Date().getMonth(),
  diaSeleccionado: null,
  filtros:         { pendiente: true, finalizado: true, cancelado: true },
  tipo:            '',
  vista:           'mes',
};

// ── Helpers ────────────────────────────────────────────────────────────
function toISO(año, mes, dia) {
  return `${año}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
}

function filtradas() {
  return state.sesiones.filter(s =>
    state.filtros[s.estado] !== false &&
    (!state.tipo || s.tipo === state.tipo)
  );
}

function porFecha(list) {
  const mapa = {};
  for (const s of list) {
    const f = s.fecha_hora_sesion?.slice(0, 10);
    if (f) (mapa[f] ??= []).push(s);
  }
  return mapa;
}

// ── Stats ──────────────────────────────────────────────────────────────
function renderStats() {
  const list   = filtradas();
  const prefix = `${state.año}-${String(state.mes + 1).padStart(2, '0')}`;
  const enMes  = list.filter(s => s.fecha_hora_sesion?.startsWith(prefix)).length;
  const pend   = list.filter(s => s.estado === 'pendiente').length;
  const elMes  = document.getElementById('statMes');
  const elPend = document.getElementById('statPend');
  if (elMes)  elMes.textContent  = enMes;
  if (elPend) elPend.textContent = pend;
}

// ── Month label ────────────────────────────────────────────────────────
function renderLabel() {
  const el = document.getElementById('monthLabel');
  if (el) el.textContent = `${MESES[state.mes]} ${state.año}`;
}

// ── Day names ──────────────────────────────────────────────────────────
function renderDayNames() {
  const el = document.getElementById('dayNames');
  if (!el) return;
  el.innerHTML = DIAS.map(d => `<div class="cal-day-name">${d}</div>`).join('');
}

// ── Month grid ─────────────────────────────────────────────────────────
function renderGrid() {
  const grid = document.getElementById('calGrid');
  if (!grid) return;

  const mapa   = porFecha(filtradas());
  const hoy    = new Date();
  const hoyISO = toISO(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
  const inicio = new Date(state.año, state.mes, 1).getDay();
  const ultimo = new Date(state.año, state.mes + 1, 0).getDate();
  const celdas = Math.ceil((inicio + ultimo) / 7) * 7;

  let html = '';
  for (let i = 0; i < celdas; i++) {
    const d      = new Date(state.año, state.mes, 1 + (i - inicio));
    const iso    = toISO(d.getFullYear(), d.getMonth(), d.getDate());
    const esMes  = d.getMonth() === state.mes;
    const sess   = mapa[iso] ?? [];

    let cls = 'cal-cell';
    if (!esMes)                       cls += ' other-month';
    if (iso === hoyISO)               cls += ' today';
    if (iso === state.diaSeleccionado) cls += ' selected';

    const MAX    = 2;
    const events = sess.slice(0, MAX).map(s => {
      const label = s.nombre_colegio ?? s.nombre_promocion ?? TIPO_LABEL[s.tipo] ?? s.tipo;
      const hora  = s.fecha_hora_sesion?.slice(11, 16) ?? '';
      return `<div class="cal-event ses-${s.estado}" title="${label}${hora ? ' — ' + hora : ''}">${label}</div>`;
    }).join('');
    const resto = sess.length - MAX;
    const mas   = resto > 0 ? `<div class="cal-event more">+${resto} más</div>` : '';

    html += `<div class="${cls}" onclick="selDia('${iso}')">
      <div class="cal-day-num">${d.getDate()}</div>
      ${events}${mas}
    </div>`;
  }

  grid.innerHTML = html;
}

// ── List view ──────────────────────────────────────────────────────────
function renderLista() {
  const container = document.getElementById('listaContainer');
  if (!container) return;

  const list = filtradas()
    .filter(s => s.fecha_hora_sesion)
    .sort((a, b) => a.fecha_hora_sesion.localeCompare(b.fecha_hora_sesion));

  if (!list.length) {
    container.innerHTML = `
      <div style="text-align:center;padding:3rem;color:var(--text-muted);font-size:.85rem;">
        <i class="bi bi-camera" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
        Sin sesiones para mostrar
      </div>`;
    return;
  }

  const grupos = {};
  for (const s of list) {
    const key = s.fecha_hora_sesion.slice(0, 7);
    (grupos[key] ??= []).push(s);
  }

  container.innerHTML = Object.entries(grupos).map(([key, sess]) => {
    const [a, m] = key.split('-').map(Number);
    const rows = sess.map(s => {
      const iso   = s.fecha_hora_sesion.slice(0, 10);
      const hora  = s.fecha_hora_sesion.slice(11, 16);
      const diaN  = DIAS[new Date(iso + 'T00:00').getDay()];
      const num   = iso.slice(8);
      const href  = s.id_contrato ? `${BASE_URL}contratos/${s.id_contrato}/sesiones` : '#';
      const tit   = s.nombre_colegio ?? s.nombre_promocion ?? '—';
      const nivel = [s.grado, s.seccion].filter(Boolean).join(' · ');
      return `
        <div class="list-row" onclick="${s.id_contrato ? `window.location.href='${href}'` : ''}">
          <div class="list-date">
            <div class="ld-d">${num}</div>
            <div class="ld-m">${diaN}</div>
          </div>
          <div class="list-info">
            <div class="li-title">${tit}</div>
            <div class="li-meta">${hora}${nivel ? ' · ' + nivel : ''} · ${TIPO_LABEL[s.tipo] ?? s.tipo}</div>
          </div>
          <span class="type-pill tp-ses-${s.estado}">${s.estado}</span>
        </div>`;
    }).join('');
    return `<div class="list-month-sep">${MESES[m - 1]} ${a}</div>${rows}`;
  }).join('');
}

// ── Side panel ─────────────────────────────────────────────────────────
function renderPanel() {
  const titulo    = document.getElementById('panelTitulo');
  const contenido = document.getElementById('panelContenido');
  if (!titulo || !contenido) return;

  const list = filtradas();
  let sess;

  if (state.diaSeleccionado) {
    const [, m, d] = state.diaSeleccionado.split('-').map(Number);
    titulo.textContent = `${d} de ${MESES[m - 1]}`;
    sess = list
      .filter(s => s.fecha_hora_sesion?.startsWith(state.diaSeleccionado))
      .sort((a, b) => a.fecha_hora_sesion.localeCompare(b.fecha_hora_sesion));
  } else {
    titulo.textContent = 'Próximas sesiones';
    const hoy = new Date().toISOString().slice(0, 10);
    sess = list
      .filter(s => s.fecha_hora_sesion >= hoy && s.estado === 'pendiente')
      .sort((a, b) => a.fecha_hora_sesion.localeCompare(b.fecha_hora_sesion))
      .slice(0, 8);
  }

  if (!sess.length) {
    contenido.innerHTML = `<div style="text-align:center;padding:1.5rem .5rem;color:var(--text-muted);font-size:.78rem;">Sin sesiones</div>`;
    return;
  }

  contenido.innerHTML = sess.map(s => {
    const href  = s.id_contrato ? `${BASE_URL}contratos/${s.id_contrato}/sesiones` : '#';
    const hora  = s.fecha_hora_sesion?.slice(11, 16) ?? '';
    const label = s.nombre_colegio ?? s.nombre_promocion ?? '—';
    return `
      <div class="ev-card" onclick="${s.id_contrato ? `window.location.href='${href}'` : ''}"
           style="cursor:${s.id_contrato ? 'pointer' : 'default'};margin-bottom:6px;">
        <div class="ev-type ${s.estado}">${TIPO_LABEL[s.tipo] ?? s.tipo}</div>
        <div class="ev-title">${label}</div>
        <div class="ev-meta">${hora ? hora + ' · ' : ''}${s.nombre_promocion ?? ''}</div>
      </div>`;
  }).join('');
}

// ── View toggle ────────────────────────────────────────────────────────
function setVista(vista) {
  state.vista = vista;
  document.getElementById('viewMes')  ?.style.setProperty('display', vista === 'mes'   ? '' : 'none');
  document.getElementById('viewLista')?.style.setProperty('display', vista === 'lista' ? '' : 'none');
  document.getElementById('btnMes')  ?.classList.toggle('active', vista === 'mes');
  document.getElementById('btnLista')?.classList.toggle('active', vista === 'lista');
  vista === 'mes' ? renderGrid() : renderLista();
}

// ── Full re-render ─────────────────────────────────────────────────────
function render() {
  renderLabel();
  renderStats();
  renderPanel();
  state.vista === 'mes' ? renderGrid() : renderLista();
}

// ── Global functions (called from inline onclick) ──────────────────────
window.selDia = function (iso) {
  state.diaSeleccionado = state.diaSeleccionado === iso ? null : iso;
  renderGrid();
  renderPanel();
};

// ── Init ───────────────────────────────────────────────────────────────
async function init() {
  renderDayNames();
  renderLabel();

  document.getElementById('prevBtn')?.addEventListener('click', () => {
    state.mes--;
    if (state.mes < 0) { state.mes = 11; state.año--; }
    state.diaSeleccionado = null;
    render();
  });
  document.getElementById('nextBtn')?.addEventListener('click', () => {
    state.mes++;
    if (state.mes > 11) { state.mes = 0; state.año++; }
    state.diaSeleccionado = null;
    render();
  });
  document.getElementById('todayBtn')?.addEventListener('click', () => {
    const hoy = new Date();
    state.año = hoy.getFullYear();
    state.mes = hoy.getMonth();
    state.diaSeleccionado = null;
    render();
  });

  document.getElementById('btnMes')  ?.addEventListener('click', () => setVista('mes'));
  document.getElementById('btnLista')?.addEventListener('click', () => setVista('lista'));

  document.querySelectorAll('.filter-cb').forEach(cb => {
    cb.addEventListener('change', () => {
      state.filtros[cb.dataset.estado] = cb.checked;
      render();
    });
  });

  document.getElementById('filterTipoCalendario')?.addEventListener('change', e => {
    state.tipo = e.target.value;
    render();
  });

  const grid = document.getElementById('calGrid');
  if (grid) grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);font-size:.82rem;">Cargando sesiones…</div>`;

  try {
    const res = await sesionApi.listar();
    state.sesiones = res.data ?? [];
  } catch (e) {
    if (grid) grid.innerHTML = `
      <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--red-text);font-size:.82rem;">
        <i class="bi bi-exclamation-circle me-1"></i>Error al cargar sesiones
      </div>`;
    return;
  }

  render();
}

init();
