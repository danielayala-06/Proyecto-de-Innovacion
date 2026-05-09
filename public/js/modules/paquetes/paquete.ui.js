import { formatters }          from '../../utils/formatters.js';
import { categoriaDesPaquete } from './paquete.state.js';

const CAT_BADGE_CLASS = {
    'Quinceañeros': 'cat-quinceaneros',
    'Cuadros':      'cat-cuadros',
    'Anuarios':     'cat-anuarios',
    'Paquetes':     'cat-paquetes',
    'Matrimonios':  'cat-matrimonios',
    'Corporativo':  'cat-corporativo',
    'Otro':         'cat-otro',
};

function _catBadge(p) {
    const cat = categoriaDesPaquete(p);
    const cls = CAT_BADGE_CLASS[cat] ?? 'cat-otro';
    return `<span class="pc-cat-badge ${cls}">${cat}</span>`;
}

function _estadoBadge(estado) {
    const activo = estado === 'ACTIVO';
    return `<span class="${activo ? 'badge-aprobada' : 'badge-rechazada'}">${activo ? 'Activo' : 'Inactivo'}</span>`;
}

export const ui = {
    renderLoading() {
        const grid = document.getElementById('paquetesGrid');
        if (!grid) return;
        grid.innerHTML = Array.from({ length: 6 }, () => `
            <div class="paquete-card" style="opacity:.4;pointer-events:none;">
                <div class="pc-header">
                    <div>
                        <div class="placeholder-glow mb-1">
                            <span class="placeholder col-4" style="height:.65rem;display:block;border-radius:10px;"></span>
                        </div>
                        <div class="placeholder-glow">
                            <span class="placeholder col-7" style="height:1rem;display:block;border-radius:4px;"></span>
                        </div>
                    </div>
                </div>
                <div class="pc-items">
                    ${[1, 2, 3].map(() => `
                    <div class="placeholder-glow mb-2">
                        <span class="placeholder col-9" style="height:.7rem;display:block;border-radius:4px;"></span>
                    </div>`).join('')}
                </div>
                <div class="pc-footer">
                    <div class="placeholder-glow">
                        <span class="placeholder col-4" style="height:1rem;display:block;border-radius:4px;"></span>
                    </div>
                </div>
            </div>`).join('');
    },

    renderError(msg = 'Error al cargar los paquetes.') {
        const grid = document.getElementById('paquetesGrid');
        if (!grid) return;
        grid.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-exclamation-circle" style="color:var(--red-text);"></i>
                ${msg}
            </div>`;
    },

    renderStats({ total, activos, promedio, maximo }) {
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        set('statTotal',   total);
        set('statActivos', activos);
        set('statPromedio', formatters.moneda(promedio));
        set('statMax',      formatters.moneda(maximo));
    },

    renderGrid(paquetes) {
        const grid = document.getElementById('paquetesGrid');
        if (!grid) return;

        if (!paquetes.length) {
            grid.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    No hay paquetes para mostrar.
                </div>`;
            return;
        }

        grid.innerHTML = paquetes.map(p => {
            const lineas  = (p.descripcion || '').split('\n').filter(Boolean);
            const desc    = lineas[0] ?? '';
            const items   = lineas.slice(1);
            const esActivo = p.estado === 'ACTIVO';

            return `
            <div class="paquete-card">
                <div class="pc-header">
                    <div>
                        ${_catBadge(p)}
                        <div class="pc-name">${p.nombre_paquete}</div>
                        ${desc ? `<div class="pc-desc">${desc}</div>` : ''}
                    </div>
                    ${_estadoBadge(p.estado)}
                </div>

                ${items.length ? `
                <div class="pc-items">
                    <div class="pc-items-title">Incluye</div>
                    ${items.map(l => `
                        <div class="pc-item-row">
                            <i class="bi bi-check2"></i>
                            <span>${l}</span>
                        </div>`).join('')}
                </div>` : ''}

                <div class="pc-footer">
                    <div class="pc-price">${formatters.moneda(p.precio)}</div>
                    <div class="pc-actions">
                        <button class="btn-icon" onclick="editarPaquete(${p.id_paquete})" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-icon danger"
                                onclick="toggleEstado(${p.id_paquete},'${p.estado}')"
                                title="${esActivo ? 'Desactivar' : 'Activar'}">
                            <i class="bi bi-${esActivo ? 'slash-circle' : 'play-circle'}"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        }).join('');
    },
};
