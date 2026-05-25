/**
 * @file    promocionesMain.js
 * @module  promociones/promocionesMain
 *
 * Módulo principal del listado de promociones escolares.
 * Carga las promociones desde la API, renderiza la tabla con filtros
 * y permite visualizar las sesiones disponibles por promoción en un modal.
 *
 * Variables globales requeridas (declaradas en la vista PHP):
 *  - `window.BASE_URL` : URL base de la aplicación.
 */

import { http }       from '../../utils/http.js';
import { formatters } from '../../utils/formatters.js';
import { alerts }     from '../../utils/alerts.js';

// ─── Estado ──────────────────────────────────────────────────────────────────

let _todas     = [];
let _pagina    = 1;
const POR_PAG  = 15;

// ─── Inicialización ───────────────────────────────────────────────────────────

async function init() {
    try {
        const res = await http.get('api/promociones');
        _todas = res.data ?? [];
        _renderStats(_todas);
        _aplicarFiltros();
    } catch (err) {
        console.error('Error cargando promociones:', err);
        alerts.error('No se pudieron cargar las promociones');
    }

    document.getElementById('searchInput').addEventListener('input',  _onFilter);
    document.getElementById('filterGrado').addEventListener('change', _onFilter);
    document.getElementById('filterActiva').addEventListener('change', _onFilter);
}

// ─── Stats ────────────────────────────────────────────────────────────────────

function _renderStats(lista) {
    document.getElementById('statTotal').textContent     = lista.length;
    document.getElementById('statActivas').textContent   = lista.filter(p => p.is_active).length;
    document.getElementById('statEstudiantes').textContent = lista.reduce((s, p) => s + (p.num_estudiantes || 0), 0);

    const colegiosUnicos = new Set(lista.map(p => p.id_colegio)).size;
    document.getElementById('statColegios').textContent  = colegiosUnicos;
}

// ─── Filtrado ─────────────────────────────────────────────────────────────────

function _onFilter() {
    _pagina = 1;
    _aplicarFiltros();
}

function _aplicarFiltros() {
    const q      = document.getElementById('searchInput').value.trim().toLowerCase();
    const grado  = document.getElementById('filterGrado').value;
    const activa = document.getElementById('filterActiva').value;

    const filtradas = _todas.filter(p => {
        if (q) {
            const haystack = [p.nombre, p.nombre_colegio, p.distrito].join(' ').toLowerCase();
            if (!haystack.includes(q)) return false;
        }
        if (grado  && p.grado     !== grado)          return false;
        if (activa !== '' && String(p.is_active ? 1 : 0) !== activa) return false;
        return true;
    });

    _renderTabla(filtradas);
    _renderPaginacion(filtradas.length);
}

// ─── Tabla ────────────────────────────────────────────────────────────────────

function _renderTabla(lista) {
    const tbody = document.getElementById('tablaBody');
    const inicio = (_pagina - 1) * POR_PAG;
    const pagina = lista.slice(inicio, inicio + POR_PAG);

    if (!pagina.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">
            <i class="bi bi-inbox" style="font-size:1.4rem;"></i><br>Sin resultados
        </td></tr>`;
        return;
    }

    tbody.innerHTML = pagina.map(_rowHtml).join('');
}

function _rowHtml(p) {
    const badgeEstado = p.is_active
        ? '<span class="badge-aprobada">Activa</span>'
        : '<span class="badge-rechazada">Inactiva</span>';

    const seccion = p.seccion ? ` — Sección ${p.seccion}` : '';

    const btnSesiones = `<button class="btn-icon" title="Ver / gestionar sesiones"
            onclick="window.verSesiones(${p.id_promocion}, ${p.id_contrato ?? 'null'}, '${_esc(p.nombre)}')"
            style="color:var(--accent);">
           <i class="bi bi-camera-fill"></i>
       </button>`;

    return `<tr>
        <td><span class="badge" style="background:var(--bg-hover);color:var(--text-secondary);font-size:.75rem;padding:2px 8px;border-radius:20px;">${_esc(p.grado)}</span></td>
        <td>
            <div style="font-weight:500;">${_esc(p.nombre_colegio ?? '—')}</div>
            <div style="font-size:.75rem;color:var(--text-muted);">${_esc(p.distrito ?? '')}</div>
        </td>
        <td>
            <div style="font-weight:500;">${_esc(p.nombre)}</div>
            <div style="font-size:.75rem;color:var(--text-muted);">${seccion}</div>
        </td>
        <td style="text-align:center;font-weight:600;">${p.num_estudiantes}</td>
        <td style="text-align:center;color:var(--text-muted);">${p.anio}</td>
        <td style="text-align:center;">${badgeEstado}</td>
        <td style="text-align:center;">${btnSesiones}</td>
    </tr>`;
}

// ─── Paginación ───────────────────────────────────────────────────────────────

function _renderPaginacion(total) {
    const totalPag = Math.ceil(total / POR_PAG);
    const info     = document.getElementById('paginaInfo');
    const btns     = document.getElementById('paginaBtns');

    info.textContent = total
        ? `Mostrando ${Math.min((_pagina - 1) * POR_PAG + 1, total)}–${Math.min(_pagina * POR_PAG, total)} de ${total}`
        : '';

    btns.innerHTML = '';
    if (totalPag <= 1) return;

    for (let i = 1; i <= totalPag; i++) {
        const btn = document.createElement('button');
        btn.className  = 'pag-btn' + (i === _pagina ? ' active' : '');
        btn.textContent = i;
        btn.onclick    = () => { _pagina = i; _aplicarFiltros(); };
        btns.appendChild(btn);
    }
}

// ─── Modal sesiones ───────────────────────────────────────────────────────────

const TIPOS_SESION = ['colegio', 'exteriores', 'estudio', 'otro'];
const TIPO_LABEL   = { colegio: 'Colegio', exteriores: 'Exteriores', estudio: 'Estudio', otro: 'Otro' };
const ESTADO_BADGE = {
    pendiente:  '<span class="badge-pendiente">Pendiente</span>',
    finalizado: '<span class="badge-aprobada">Finalizado</span>',
    cancelado:  '<span class="badge-rechazada">Cancelado</span>',
};

let _modalState = { idPromocion: null, idContrato: null };

window.verSesiones = async function(idPromocion, idContrato, nombrePromocion) {
    _modalState = { idPromocion, idContrato };

    const modalEl = document.getElementById('modalSesiones');
    const modal   = bootstrap.Modal.getOrCreateInstance(modalEl);
    const body    = document.getElementById('modalSesionesBody');
    const titulo  = document.getElementById('modalSesionesTitulo');
    const btnIr   = document.getElementById('btnIrSesiones');

    titulo.textContent = nombrePromocion;
    btnIr.style.display = idContrato ? '' : 'none';
    if (idContrato) {
        btnIr.href = `${BASE_URL}index.php/contratos/${idContrato}/sesiones`;
    }
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="bi bi-arrow-repeat"></i> Cargando...</div>';
    modal.show();

    await _recargarModal();
};

async function _recargarModal() {
    const { idPromocion } = _modalState;
    const body = document.getElementById('modalSesionesBody');
    try {
        const [limitesRes, sesionesRes] = await Promise.all([
            Promise.all(TIPOS_SESION.map(t =>
                http.get(`api/sesiones/limite/${idPromocion}`, { tipo: t })
                    .then(r => ({ tipo: t, ...r.data }))
                    .catch(() => ({ tipo: t, permitidas: 0, usadas: 0, puede_crear: false }))
            )),
            http.get('api/sesiones', { id_promocion: idPromocion }),
        ]);

        body.innerHTML = _renderModalBody(limitesRes, sesionesRes.data ?? []);
    } catch (err) {
        console.error('Error cargando sesiones:', err);
        body.innerHTML = '<div style="color:var(--red-text);padding:1rem;">Error al cargar las sesiones.</div>';
    }
}

function _renderModalBody(limites, sesiones) {
    const conLimite   = limites.filter(l => l.permitidas > 0);
    const puedeCrear  = conLimite.some(l => l.puede_crear);

    // Tarjetas de límite por tipo
    const limiteHtml = conLimite.length
        ? `<div style="margin-bottom:1.2rem;">
               <p style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem;">Sesiones disponibles por tipo</p>
               <div class="row g-2">
                   ${conLimite.map(l => `
                   <div class="col-6 col-md-3">
                       <div style="background:var(--bg-hover);border:1px solid var(--border-color);border-radius:8px;padding:10px;text-align:center;">
                           <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:4px;">${TIPO_LABEL[l.tipo]}</div>
                           <div style="font-size:1.3rem;font-weight:700;color:${l.puede_crear ? 'var(--green-text)' : 'var(--red-text)'};">
                               ${l.usadas}/${l.permitidas}
                           </div>
                           <div style="font-size:.68rem;color:var(--text-muted);">usadas / permitidas</div>
                       </div>
                   </div>`).join('')}
               </div>
           </div>`
        : `<div style="font-size:.82rem;color:var(--text-muted);margin-bottom:1rem;padding:10px;background:var(--bg-hover);border-radius:8px;">
               <i class="bi bi-info-circle me-1"></i>
               Esta promoción no tiene configuración de sesiones. Verifica que la cotización incluya paquetes con sesiones.
           </div>`;

    // Formulario de nueva sesión (solo si hay cupo disponible)
    const formNueva = puedeCrear
        ? `<div style="margin-bottom:1.2rem;padding:12px;background:var(--bg-hover);border:1px solid var(--border-color);border-radius:8px;">
               <p style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem;" style="margin-bottom:.6rem;">Nueva sesión</p>
               <div class="row g-2">
                   <div class="col-12 col-md-4">
                       <label style="font-size:.78rem;margin-bottom:3px;">Tipo</label>
                       <select class="form-select form-select-sm" id="nsTipo">
                           <option value="">Seleccionar...</option>
                           ${conLimite.filter(l => l.puede_crear).map(l =>
                               `<option value="${l.tipo}">${TIPO_LABEL[l.tipo]}</option>`
                           ).join('')}
                       </select>
                   </div>
                   <div class="col-6 col-md-4">
                       <label style="font-size:.78rem;margin-bottom:3px;">Fecha</label>
                       <input type="date" class="form-control form-control-sm" id="nsFecha">
                   </div>
                   <div class="col-6 col-md-4">
                       <label style="font-size:.78rem;margin-bottom:3px;">Hora</label>
                       <input type="time" class="form-control form-control-sm" id="nsHora" value="08:00">
                   </div>
                   <div class="col-12">
                       <label style="font-size:.78rem;margin-bottom:3px;">Observaciones <span style="color:var(--text-muted);">(opcional)</span></label>
                       <input type="text" class="form-control form-control-sm" id="nsObs" placeholder="Lugar, indicaciones, etc.">
                   </div>
                   <div class="col-12 d-flex justify-content-end">
                       <button class="btn btn-primary btn-sm" onclick="window.crearSesion()">
                           <i class="bi bi-plus-circle me-1"></i>Agendar sesión
                       </button>
                   </div>
               </div>
           </div>`
        : '';

    // Lista de sesiones existentes
    const sesionesHtml = `
        <p style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem;">Sesiones agendadas${sesiones.length ? ` (${sesiones.length})` : ''}</p>
        ${sesiones.length
            ? `<div style="max-height:220px;overflow-y:auto;border:1px solid var(--border-color);border-radius:8px;">
                   ${sesiones.map(s => `
                   <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-bottom:1px solid var(--border-color);">
                       <i class="bi bi-camera" style="color:var(--accent);font-size:1rem;flex-shrink:0;"></i>
                       <div style="flex:1;min-width:0;">
                           <div style="font-weight:500;font-size:.83rem;">${TIPO_LABEL[s.tipo] ?? s.tipo}</div>
                           <div style="font-size:.75rem;color:var(--text-muted);">${_fechaHora(s.fecha_hora_sesion)}</div>
                       </div>
                       ${ESTADO_BADGE[s.estado] ?? s.estado}
                   </div>`).join('')}
               </div>`
            : `<div style="font-size:.83rem;color:var(--text-muted);padding:8px 0;">Aún no hay sesiones agendadas.</div>`
        }`;

    return limiteHtml + formNueva + sesionesHtml;
}

window.crearSesion = async function() {
    const { idPromocion } = _modalState;
    const tipo  = document.getElementById('nsTipo')?.value;
    const fecha = document.getElementById('nsFecha')?.value;
    const hora  = document.getElementById('nsHora')?.value || '08:00';
    const obs   = document.getElementById('nsObs')?.value?.trim() || null;

    if (!tipo) { alerts.warning('Selecciona el tipo de sesión'); return; }
    if (!fecha) { alerts.warning('Selecciona la fecha'); return; }

    try {
        await http.post('api/sesiones', {
            id_promocion:      idPromocion,
            tipo,
            fecha_hora_sesion: `${fecha} ${hora}:00`,
            observaciones:     obs,
        });
        alerts.success('Sesión agendada');
        await _recargarModal();
    } catch (err) {
        alerts.error(err.message || 'Error al crear la sesión');
    }
};

// ─── Helpers ──────────────────────────────────────────────────────────────────

function _fechaHora(str) {
    if (!str) return '—';
    const [fecha, hora] = str.split(' ');
    return `${formatters.fecha(fecha)}${hora ? ' · ' + hora.slice(0, 5) : ''}`;
}

function _esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ─── Boot ─────────────────────────────────────────────────────────────────────

init();
