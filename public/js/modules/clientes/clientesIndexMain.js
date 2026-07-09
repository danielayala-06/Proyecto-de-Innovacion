import { colegioApi } from '../../api/colegio.api.js';
import { alerts }     from '../../utils/alerts.js';
import { formatters } from '../../utils/formatters.js';

// ─── Estado ──────────────────────────────────────────────────────────────────
let _todos     = [];
let _filtrados = [];
let _sortCampo = 'nombre_colegio';
let _sortAsc   = true;
let _detalle   = null;   // colegio completo cargado en el modal

let _modalDetalle = null;
let _modalEditar  = null;

// ─── Carga inicial ────────────────────────────────────────────────────────────
async function _cargar() {
    try {
        const res = await colegioApi.listar();
        _todos = res.data ?? [];
        _aplicarFiltros();
    } catch {
        document.getElementById('tablaBody').innerHTML =
            '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--red-text);">Error al cargar los colegios.</td></tr>';
    }
}

// ─── Filtros y ordenamiento ───────────────────────────────────────────────────
function _aplicarFiltros() {
    const q      = (document.getElementById('searchInput')?.value  ?? '').toLowerCase().trim();
    const estado = (document.getElementById('filterEstado')?.value  ?? '');

    _filtrados = _todos.filter(c => {
        if (estado && c.estado !== estado) return false;
        if (q) {
            const hay = [c.nombre_colegio, c.distrito, c.provincia,
                         c.contacto?.nombres, c.contacto?.apellidos]
                .some(v => v && v.toLowerCase().includes(q));
            if (!hay) return false;
        }
        return true;
    });

    _filtrados.sort((a, b) => {
        let va = a[_sortCampo] ?? '';
        let vb = b[_sortCampo] ?? '';
        if (typeof va === 'string') va = va.toLowerCase();
        if (typeof vb === 'string') vb = vb.toLowerCase();
        return _sortAsc ? (va > vb ? 1 : -1) : (va < vb ? 1 : -1);
    });

    _renderStats();
    _renderTabla();
}

// ─── Stats ────────────────────────────────────────────────────────────────────
function _renderStats() {
    const activos = _filtrados.filter(c => c.estado === 'ACTIVO').length;
    const promos  = _filtrados.reduce((s, c) => s + (c.total_promociones ?? 0), 0);
    document.getElementById('statTotal').textContent   = _filtrados.length;
    document.getElementById('statActivos').textContent = activos;
    document.getElementById('statPromos').textContent  = promos;
}

// ─── Tabla ────────────────────────────────────────────────────────────────────
function _renderTabla() {
    const tbody = document.getElementById('tablaBody');
    if (!_filtrados.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-secondary);">Sin resultados.</td></tr>';
        return;
    }

    tbody.innerHTML = _filtrados.map(c => {
        const ubicacion = [c.distrito, c.provincia].filter(Boolean).join(', ') || '—';
        const contacto  = c.contacto
            ? `${c.contacto.nombres ?? ''} ${c.contacto.apellidos ?? ''}`.trim() || '—'
            : '<span style="color:var(--text-secondary);">Sin contacto</span>';

        const promo = c.ultima_promo
            ? `<span style="font-size:.82rem;">${c.ultima_promo.grado} ${c.ultima_promo.seccion ?? ''} · ${c.ultima_promo.anio}</span>`
            : '<span style="color:var(--text-secondary);font-size:.82rem;">Sin registros</span>';

        const badge = c.estado === 'ACTIVO'
            ? '<span class="badge-estado activo">Activo</span>'
            : '<span class="badge-estado inactivo">Inactivo</span>';

        return `<tr onclick="abrirDetalle(${c.id_colegio})" style="cursor:pointer;">
            <td style="font-weight:600;">${_esc(c.nombre_colegio)}</td>
            <td style="color:var(--text-secondary);font-size:.85rem;">${_esc(ubicacion)}</td>
            <td>${promo}</td>
            <td style="font-size:.85rem;">${contacto}</td>
            <td style="text-align:center;font-weight:600;">${c.total_promociones ?? 0}</td>
            <td style="text-align:center;">${badge}</td>
            <td style="text-align:center;" onclick="event.stopPropagation()">
                <button class="kebab-btn" onclick="_menuColegio(event,${c.id_colegio})">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
            </td>
        </tr>`;
    }).join('');

    _actualizarIconosSort();
}

// ─── Modal detalle ────────────────────────────────────────────────────────────
window.abrirDetalle = async function (id) {
    _detalle = null;
    document.getElementById('detNombre').textContent    = 'Cargando...';
    document.getElementById('detUbicacion').textContent = '';
    document.getElementById('detEstadoBadge').textContent = '';
    document.getElementById('detPromociones').innerHTML =
        '<div style="text-align:center;padding:2rem;color:var(--text-secondary);"><div class="spinner-border spinner-border-sm me-2"></div>Cargando...</div>';
    document.getElementById('detProductos').innerHTML   = '';
    document.getElementById('detContacto').innerHTML    = '';
    _tabActivo('promo');
    _modalDetalle?.show();

    try {
        const res = await colegioApi.obtener(id);
        _detalle  = res.data;
        _poblarDetalle(_detalle);
    } catch {
        document.getElementById('detNombre').textContent = 'Error al cargar.';
    }
};

function _poblarDetalle(c) {
    document.getElementById('detNombre').textContent    = c.nombre_colegio;
    document.getElementById('detUbicacion').textContent =
        [c.distrito, c.provincia].filter(Boolean).join(', ') || '';

    const badge = document.getElementById('detEstadoBadge');
    badge.textContent = c.estado === 'ACTIVO' ? 'Activo' : 'Inactivo';
    badge.className   = `badge-estado ${c.estado === 'ACTIVO' ? 'activo' : 'inactivo'}`;

    _renderPromociones(c.promociones ?? []);
    _renderProductos(c.productos ?? []);
    _renderContacto(c.promociones ?? []);
}

function _renderPromociones(promos) {
    const el = document.getElementById('detPromociones');
    if (!promos.length) {
        el.innerHTML = '<p style="color:var(--text-secondary);font-size:.85rem;padding:.5rem 0;">Este colegio no tiene promociones registradas.</p>';
        return;
    }

    el.innerHTML = promos.map(p => {
        const titulo  = `${p.grado}${p.seccion ? ' ' + p.seccion : ''} · ${p.anio}`;
        const nombre  = p.nombre ? `<span style="font-size:.8rem;color:var(--text-secondary);">${_esc(p.nombre)}</span>` : '';
        const alumnos = `<span style="font-size:.8rem;">${p.num_estudiantes} estudiantes</span>`;

        let badgeCont = '';
        if (p.id_contrato) {
            const estados = { ACTIVO:'badge-contrato-activo', ARCHIVADO:'badge-contrato-arch', CANCELADO:'badge-contrato-canc' };
            const cls = estados[p.con_estado] ?? '';
            badgeCont = `<span class="badge-estado ${cls}" style="font-size:.72rem;">${p.con_estado}</span>`;
        } else if (p.id_cotizacion) {
            const est = { PENDIENTE:'amber', APROBADA:'green', RECHAZADA:'red', EXPIRADA:'gray' };
            const cls = est[p.cot_estado] ?? 'gray';
            badgeCont = `<span class="badge-estado ${cls}" style="font-size:.72rem;">Cot. ${p.cot_estado}</span>`;
        } else {
            badgeCont = '<span style="font-size:.72rem;color:var(--text-secondary);">Sin cotización</span>';
        }

        const total = p.total_estimado
            ? `<span style="font-size:.8rem;font-weight:600;">${formatters.moneda(p.total_estimado)}</span>`
            : '';

        const linkCon = p.id_contrato
            ? `<a href="${BASE_URL}contratos/${p.id_contrato}" style="font-size:.78rem;color:var(--blue-text);text-decoration:none;" onclick="event.stopPropagation()">
                   <i class="bi bi-file-earmark-text me-1"></i>Ver contrato
               </a>`
            : '';

        return `
        <div style="border:1px solid var(--border);border-radius:8px;padding:.75rem 1rem;margin-bottom:.5rem;background:var(--bg-elevated,var(--surface2));">
            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                <div>
                    <div style="font-weight:600;font-size:.9rem;">${_esc(titulo)}</div>
                    ${nombre}
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">${badgeCont} ${total}</div>
            </div>
            <div class="d-flex align-items-center gap-3 mt-2">
                ${alumnos}
                ${p.contacto ? `<span style="font-size:.8rem;color:var(--text-secondary);">
                    <i class="bi bi-person me-1"></i>${_esc(p.contacto.nombres ?? '')} · ${_esc(p.contacto.telefono ?? '')}
                </span>` : ''}
                ${linkCon}
            </div>
        </div>`;
    }).join('');
}

function _renderProductos(productos) {
    const el = document.getElementById('detProductos');
    if (!productos.length) {
        el.innerHTML = '<p style="color:var(--text-secondary);font-size:.85rem;padding:.5rem 0;">No hay productos registrados para este colegio.</p>';
        return;
    }

    el.innerHTML = `
    <table style="width:100%;font-size:.83rem;border-collapse:collapse;">
        <thead>
            <tr style="color:var(--text-secondary);font-size:.75rem;letter-spacing:.05em;text-transform:uppercase;">
                <th style="padding:.4rem .5rem;text-align:left;border-bottom:1px solid var(--border);">Producto</th>
                <th style="padding:.4rem .5rem;text-align:center;border-bottom:1px solid var(--border);">Promos</th>
                <th style="padding:.4rem .5rem;text-align:right;border-bottom:1px solid var(--border);">Precio</th>
                <th style="padding:.4rem .5rem;text-align:center;border-bottom:1px solid var(--border);">Últ. año</th>
            </tr>
        </thead>
        <tbody>
        ${productos.map(p => `
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:.5rem .5rem;">
                    <div style="font-weight:600;">${_esc(p.nombre_paquete)}</div>
                    ${p.categoria ? `<div style="font-size:.75rem;color:var(--text-secondary);">${_esc(p.categoria)}</div>` : ''}
                </td>
                <td style="padding:.5rem .5rem;text-align:center;">${p.en_promociones}</td>
                <td style="padding:.5rem .5rem;text-align:right;font-variant-numeric:tabular-nums;">${formatters.moneda(p.precio_unitario)}</td>
                <td style="padding:.5rem .5rem;text-align:center;">${p.ultimo_anio}</td>
            </tr>`).join('')}
        </tbody>
    </table>`;
}

function _renderContacto(promos) {
    const el = document.getElementById('detContacto');
    // Toma el contacto de la última promoción que lo tenga
    const conPromo = promos.find(p => p.contacto);
    if (!conPromo?.contacto) {
        el.innerHTML = '<p style="color:var(--text-secondary);font-size:.85rem;padding:.5rem 0;">Sin contacto registrado.</p>';
        return;
    }
    const ct = conPromo.contacto;
    el.innerHTML = `
    <div style="padding:.25rem 0;">
        <div style="margin-bottom:.75rem;">
            <div style="font-size:.75rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Nombre</div>
            <div style="font-weight:600;">${_esc(`${ct.nombres ?? ''} ${ct.apellidos ?? ''}`.trim())}</div>
        </div>
        ${ct.telefono ? `<div>
            <div style="font-size:.75rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Teléfono</div>
            <div><a href="tel:${_esc(ct.telefono)}" style="color:var(--blue-text);text-decoration:none;">${_esc(ct.telefono)}</a></div>
        </div>` : ''}
        <div style="margin-top:.75rem;font-size:.78rem;color:var(--text-secondary);">
            Vinculado a la promoción: <strong>${conPromo.grado}${conPromo.seccion ? ' ' + conPromo.seccion : ''} · ${conPromo.anio}</strong>
        </div>
    </div>`;
}

// ─── Tabs del modal ───────────────────────────────────────────────────────────
window._tabActivo = function (tab) {
    ['promo', 'prod', 'contacto'].forEach(t => {
        document.getElementById(`tab-${t}`).style.display          = t === tab ? '' : 'none';
        document.getElementById(`tab-${t}-btn`).classList.toggle('active', t === tab);
    });
};

// ─── Edición ─────────────────────────────────────────────────────────────────
window._abrirEditar = function () {
    if (!_detalle) return;
    document.getElementById('eId').value       = _detalle.id_colegio;
    document.getElementById('eNombre').value   = _detalle.nombre_colegio ?? '';
    document.getElementById('eDistrito').value = _detalle.distrito        ?? '';
    document.getElementById('eProvincia').value= _detalle.provincia       ?? '';
    _modalDetalle?.hide();
    _modalEditar?.show();
};

window.guardarEdicion = async function () {
    const id       = parseInt(document.getElementById('eId').value);
    const nombre   = document.getElementById('eNombre').value.trim();
    const distrito = document.getElementById('eDistrito').value.trim();
    const provincia= document.getElementById('eProvincia').value.trim();

    if (!nombre) { alerts.error('El nombre del colegio es obligatorio.'); return; }

    try {
        await colegioApi.actualizar(id, { nombre_colegio: nombre, distrito, provincia });
        _modalEditar?.hide();
        alerts.ok('Colegio actualizado.');
        await _cargar();
    } catch (err) {
        alerts.error(err.message || 'Error al guardar.');
    }
};

// ─── Kebab menú ───────────────────────────────────────────────────────────────
let _menuEl = null;

window._menuColegio = function (e, id) {
    _cerrarMenu();
    const colegio = _todos.find(c => c.id_colegio === id);
    if (!colegio) return;

    const items = [
        `<button class="cot-row-dropdown-item" onclick="_cerrarMenu();abrirDetalle(${id})">
            <i class="bi bi-eye" style="color:var(--blue-text);"></i>Ver detalle
         </button>`,
        `<button class="cot-row-dropdown-item" onclick="_cerrarMenu();abrirDetalle(${id});setTimeout(()=>_abrirEditar(),800)">
            <i class="bi bi-pencil" style="color:var(--amber-text);"></i>Editar datos
         </button>`,
    ];

    _menuEl = document.createElement('div');
    _menuEl.className = 'cot-row-dropdown';
    _menuEl.innerHTML = items.join('');

    const rect = e.currentTarget.getBoundingClientRect();
    _menuEl.style.cssText = `position:fixed;top:${rect.bottom + 4}px;right:${window.innerWidth - rect.right}px;z-index:9999;min-width:170px;`;
    document.body.appendChild(_menuEl);
    setTimeout(() => document.addEventListener('click', _cerrarMenu, { once: true }), 0);
};

window._cerrarMenu = function () {
    _menuEl?.remove();
    _menuEl = null;
};

// ─── Ordenar ─────────────────────────────────────────────────────────────────
window.sortBy = function (campo) {
    if (_sortCampo === campo) { _sortAsc = !_sortAsc; }
    else { _sortCampo = campo; _sortAsc = true; }
    _aplicarFiltros();
};

function _actualizarIconosSort() {
    document.querySelectorAll('.sort-icon').forEach(el => {
        const campo = el.id.replace('sort-', '');
        el.className = 'bi ms-1 sort-icon ' + (campo === _sortCampo
            ? (_sortAsc ? 'bi-arrow-up' : 'bi-arrow-down')
            : 'bi-arrow-down-up');
    });
}

// ─── Utilidades ───────────────────────────────────────────────────────────────
function _esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─── Init ─────────────────────────────────────────────────────────────────────
function init() {
    _modalDetalle = new bootstrap.Modal(document.getElementById('modalDetalle'));
    _modalEditar  = new bootstrap.Modal(document.getElementById('modalEditar'));

    document.getElementById('searchInput')?.addEventListener('input',  _aplicarFiltros);
    document.getElementById('filterEstado')?.addEventListener('change', _aplicarFiltros);

    _cargar();
}

init();
