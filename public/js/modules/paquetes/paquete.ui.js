/**
 * @file    paquete.ui.js
 * @module  modules/paquetes/ui
 *
 * Capa de renderizado DOM del módulo de paquetes.
 * Todas las funciones manipulan el DOM directamente; ninguna realiza
 * llamadas a la API ni modifica el estado global.
 *
 * Elementos DOM requeridos (IDs):
 *  - `paquetesGrid`                                       : contenedor principal de tarjetas.
 *  - `statTotal`, `statActivos`, `statInactivos`,
 *    `statPromedio`, `statMax`                            : tarjetas de resumen.
 */

import { formatters }                                        from '../../utils/formatters.js';
import { categoriaDesPaquete, agruparPorNivel, NIVEL_LABEL } from './paquete.state.js';

// ─────────────────────────────────────────────────────────────────────────────
// CONSTANTES INTERNAS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Mapa de nombre de categoría → clase CSS del badge de categoría.
 *
 * @type {Object<string, string>}
 */
const CAT_BADGE_CLASS = {
    'Quinceañeros': 'cat-quinceaneros',
    'Cuadros':      'cat-cuadros',
    'Anuarios':     'cat-anuarios',
    'Paquetes':     'cat-paquetes',
    'Matrimonios':  'cat-matrimonios',
    'Corporativo':  'cat-corporativo',
    'Otro':         'cat-otro',
};

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS PRIVADOS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Genera el HTML del badge de categoría de un paquete.
 *
 * @param {Object} p - Objeto paquete de la API.
 * @returns {string} HTML del `<span>` con la clase de categoría.
 */
function _catBadge(p) {
    const cat = categoriaDesPaquete(p);
    const cls = CAT_BADGE_CLASS[cat] ?? 'cat-otro';
    return `<span class="pc-cat-badge ${cls}">${cat}</span>`;
}

/**
 * Genera el HTML del badge de estado activo/inactivo.
 *
 * @param {string} estado - `'ACTIVO'` o `'INACTIVO'`.
 * @returns {string} HTML del `<span>` con el badge correspondiente.
 */
function _estadoBadge(estado) {
    const activo = estado === 'ACTIVO';
    return `<span class="${activo ? 'badge-aprobada' : 'badge-rechazada'}">${activo ? 'Activo' : 'Inactivo'}</span>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// UI PÚBLICA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Colección de funciones de renderizado para el módulo de paquetes.
 *
 * @namespace ui
 */
export const ui = {

    /**
     * Muestra un skeleton de carga en el grid de paquetes.
     * Renderiza 6 tarjetas placeholder semitransparentes.
     *
     * @returns {void}
     */
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

    /**
     * Muestra un mensaje de error en el grid de paquetes.
     *
     * @param {string} [msg='Error al cargar los paquetes.'] - Mensaje de error a mostrar.
     * @returns {void}
     */
    renderError(msg = 'Error al cargar los paquetes.') {
        const grid = document.getElementById('paquetesGrid');
        if (!grid) return;
        grid.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-exclamation-circle" style="color:var(--red-text);"></i>
                ${msg}
            </div>`;
    },

    /**
     * Actualiza las tarjetas de estadísticas superiores.
     *
     * @param {{
     *   total:     number,
     *   activos:   number,
     *   inactivos: number,
     *   promedio:  number,
     *   maximo:    number
     * }} stats - Objeto de estadísticas calculado por {@link calcularStats}.
     * @returns {void}
     */
    renderStats({ total, activos, inactivos, promedio, maximo }) {
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        set('statTotal',    total);
        set('statActivos',  activos);
        set('statInactivos', inactivos);
        set('statPromedio', formatters.moneda(promedio));
        set('statMax',      formatters.moneda(maximo));
    },

    /**
     * Renderiza la cuadrícula de tarjetas de paquetes, agrupadas por nivel.
     *
     * Estructura del HTML generado:
     *  - Una sección `.nivel-section` por cada nivel con paquetes.
     *  - Dentro de cada sección: encabezado `.nivel-heading` + grid `.nivel-grid`.
     *  - Cada paquete se renderiza como `.paquete-card` con: badge de categoría,
     *    nombre, descripción, lista de ítems incluidos (líneas 2+ de `descripcion`),
     *    precio y botones de editar/toggle estado.
     *  - Los paquetes inactivos reciben la clase adicional `.pc-inactivo`.
     *
     * @param {Array<Object>} paquetes - Paquetes filtrados a renderizar (ya ordenados).
     * @returns {void}
     */
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

        const grupos   = agruparPorNivel(paquetes);
        const sections = [];

        for (const [nivel, lista] of grupos) {
            const label = NIVEL_LABEL[nivel] ?? nivel;
            const cards = lista.map(p => {
                const lineas   = (p.descripcion || '').split('\n').filter(Boolean);
                const desc     = lineas[0] ?? '';
                const items    = lineas.slice(1);
                const esActivo = p.estado === 'ACTIVO';

                return `
                <div class="paquete-card${esActivo ? '' : ' pc-inactivo'}">
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

            sections.push(`
                <div class="nivel-section">
                    <div class="nivel-heading"><span>${label}</span></div>
                    <div class="nivel-grid">${cards}</div>
                </div>`);
        }

        grid.innerHTML = sections.join('');
    },
};
