/**
 * calendarioUI.js
 * Render del DOM y apertura de modales.
 * Importa únicamente desde calendarioManager.
 */

import {
    getHoy, getEventos, getCurYear, getCurMonth, getSelDate, getVista,
    MESES, DIAS, getLbl, getEventosMes, getEventosPendientes,
    setSelDate,
} from './calendarioManager.js';

// ── Filtros activos (lee estado de checkboxes del DOM) ─────────────
export function getFiltros() {
    return [...document.querySelectorAll('.filter-cb:checked')].map(cb => cb.dataset.type);
}

// ── Render principal ───────────────────────────────────────────────
export function render() {
    _renderStats();
    getVista() === 'mes' ? _renderMes() : _renderLista();
    _renderPanel(getSelDate());
}

// ── Stats ──────────────────────────────────────────────────────────
function _renderStats() {
    document.getElementById('statMes').textContent  = getEventosMes().length;
    document.getElementById('statPend').textContent = getEventosPendientes().length;
}

// ── Vista mes ──────────────────────────────────────────────────────
function _renderMes() {
    const year   = getCurYear();
    const month  = getCurMonth();
    const hoy    = getHoy();
    const hoyStr = hoy.toISOString().slice(0, 10);
    const selDate = getSelDate();
    const eventos = getEventos();
    const f       = getFiltros();

    document.getElementById('monthLabel').textContent =
        `${MESES[month]} ${year}`;
    document.getElementById('dayNames').innerHTML =
        DIAS.map(d => `<div class="cal-day-name">${d}</div>`).join('');

    const first = new Date(year, month, 1).getDay();
    const dias  = new Date(year, month + 1, 0).getDate();
    const prev  = new Date(year, month, 0).getDate();
    const total = Math.ceil((first + dias) / 7) * 7;
    const grid  = document.getElementById('calGrid');
    grid.innerHTML = '';

    for (let i = 0; i < total; i++) {
        let dia, mes, anio, otro = false;

        if (i < first) {
            dia = prev - first + i + 1; mes = month - 1; anio = year;
            if (mes < 0) { mes = 11; anio--; }
            otro = true;
        } else if (i >= first + dias) {
            dia = i - first - dias + 1; mes = month + 1; anio = year;
            if (mes > 11) { mes = 0; anio++; }
            otro = true;
        } else {
            dia = i - first + 1; mes = month; anio = year;
        }

        const ds     = `${anio}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        const evDia  = eventos.filter(e => e.fecha === ds && f.includes(e.tipo));
        const cell   = document.createElement('div');

        cell.className = [
            'cal-cell',
            otro           ? 'other-month' : '',
            ds === hoyStr  ? 'today'        : '',
            ds === selDate ? 'selected'     : '',
        ].filter(Boolean).join(' ');
        cell.dataset.date = ds;

        const MAX = 2;
        let evHtml = evDia.slice(0, MAX).map(e =>
            `<div class="cal-event ${e.tipo}" title="${e.titulo} — ${e.cliente}">${e.titulo}</div>`
        ).join('');
        if (evDia.length > MAX) evHtml += `<div class="cal-event more">+${evDia.length - MAX} más</div>`;

        cell.innerHTML = `<div class="cal-day-num">${dia}</div>${evHtml}`;
        cell.addEventListener('click', () => {
            setSelDate(getSelDate() === ds ? null : ds);
            render();
        });
        grid.appendChild(cell);
    }
}

// ── Vista lista ────────────────────────────────────────────────────
function _renderLista() {
    const f     = getFiltros();
    const lista = getEventos()
        .filter(e => f.includes(e.tipo))
        .sort((a, b) => a.fecha.localeCompare(b.fecha));
    const cont  = document.getElementById('listaContainer');

    if (!lista.length) {
        cont.innerHTML = '<div style="color:var(--text-muted);font-size:0.82rem;text-align:center;padding:2rem;">Sin eventos</div>';
        return;
    }

    let prevMes = '', html = '';
    lista.forEach(ev => {
        const d  = new Date(ev.fecha + 'T00:00:00');
        const mk = `${MESES[d.getMonth()]} ${d.getFullYear()}`;
        if (mk !== prevMes) {
            html += `<div class="list-month-sep">${mk}</div>`;
            prevMes = mk;
        }
        html += `
        <div class="list-row" onclick="abrirEdicion(${ev.id})">
            <div class="list-date">
                <div class="ld-d">${d.getDate()}</div>
                <div class="ld-m">${MESES[d.getMonth()].slice(0, 3).toUpperCase()}</div>
            </div>
            <div class="list-info">
                <div class="li-title">${ev.titulo}</div>
                <div class="li-meta">
                    <i class="bi bi-clock" style="font-size:0.6rem;"></i> ${ev.hora || '--:--'}
                    · <i class="bi bi-person" style="font-size:0.6rem;"></i> ${ev.cliente}
                    ${ev.monto ? ` · S/ ${Number(ev.monto).toFixed(2)}` : ''}
                </div>
            </div>
            <span class="type-pill tp-${ev.tipo}">${getLbl(ev.tipo)}</span>
            ${ev.estado === 'pendiente' ? '<span class="badge-pend ms-1">Pendiente</span>' : ''}
        </div>`;
    });
    cont.innerHTML = html;
}

// ── Panel lateral ──────────────────────────────────────────────────
function _renderPanel(ds) {
    const f    = getFiltros();
    const tit  = document.getElementById('panelTitulo');
    const cont = document.getElementById('panelContenido');
    let lista;

    if (ds) {
        const d = new Date(ds + 'T00:00:00');
        tit.textContent = `${d.getDate()} de ${MESES[d.getMonth()]}`;
        lista = getEventos().filter(e => e.fecha === ds && f.includes(e.tipo));
    } else {
        tit.textContent  = 'Próximos eventos';
        const hoyStr = getHoy().toISOString().slice(0, 10);
        lista = getEventos()
            .filter(e => e.fecha >= hoyStr && f.includes(e.tipo))
            .sort((a, b) => a.fecha.localeCompare(b.fecha))
            .slice(0, 5);
    }

    if (!lista.length) {
        cont.innerHTML = '<div style="color:var(--text-muted);font-size:0.78rem;text-align:center;padding:1rem 0;">Sin eventos</div>';
        return;
    }

    cont.innerHTML = lista.map(ev => `
        <div class="ev-card" onclick="abrirEdicion(${ev.id})">
            <div class="ev-type ${ev.tipo}">${getLbl(ev.tipo)}</div>
            <div class="ev-title">${ev.titulo}</div>
            <div class="ev-meta">
                <i class="bi bi-clock" style="font-size:0.6rem;"></i> ${ev.hora || '--:--'} · ${ev.cliente}
            </div>
            ${ev.monto ? `<div class="ev-monto">S/ ${Number(ev.monto).toFixed(2)}</div>` : ''}
            ${ev.estado === 'pendiente' ? '<span class="badge-pend" style="display:inline-block;margin-top:3px;">Pendiente</span>' : ''}
        </div>`).join('');
}

// ── Apertura de modales ────────────────────────────────────────────
export function abrirModalNuevo(ds) {
    const hoyStr = getHoy().toISOString().slice(0, 10);
    document.getElementById('modalTitulo').textContent  = 'Nuevo evento';
    document.getElementById('eId').value      = '';
    document.getElementById('eTitulo').value  = '';
    document.getElementById('eTipo').value    = 'cotizacion';
    document.getElementById('eEstado').value  = 'pendiente';
    document.getElementById('eFecha').value   = ds || hoyStr;
    document.getElementById('eHora').value    = '';
    document.getElementById('eCliente').value = '';
    document.getElementById('eMonto').value   = '';
    document.getElementById('eNotas').value   = '';
    document.getElementById('btnEliminar').style.display = 'none';
    new bootstrap.Modal(document.getElementById('modalEvento')).show();
}

export function abrirModalEdicion(id) {
    const ev = getEventos().find(e => e.id === id);
    if (!ev) return;
    document.getElementById('modalTitulo').textContent  = 'Editar evento';
    document.getElementById('eId').value      = id;
    document.getElementById('eTitulo').value  = ev.titulo;
    document.getElementById('eTipo').value    = ev.tipo;
    document.getElementById('eEstado').value  = ev.estado;
    document.getElementById('eFecha').value   = ev.fecha;
    document.getElementById('eHora').value    = ev.hora   || '';
    document.getElementById('eCliente').value = ev.cliente || '';
    document.getElementById('eMonto').value   = ev.monto  || '';
    document.getElementById('eNotas').value   = ev.notas  || '';
    document.getElementById('btnEliminar').style.display = '';
    new bootstrap.Modal(document.getElementById('modalEvento')).show();
}

// ── Exponer al scope global (usados en onclick del HTML) ───────────
window.abrirNuevo   = abrirModalNuevo;
window.abrirEdicion = abrirModalEdicion;
