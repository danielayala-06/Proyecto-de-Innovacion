// Dashboard module — Ronceros Fotografía
// Los datos provienen de CHART_DATA, inyectado por Home::index() como JSON.

const PALETA = {
    s1: '#2a78d6',
    s2: '#1baf7a',
    s3: '#eda100',
    s4: '#008300',
    s5: '#4a3aa7',
    s6: '#e34948',
    s7: '#e87ba4',
    s8: '#eb6834',
};

const GRID_COLOR = 'rgba(0,0,0,0.06)';
const TICK_COLOR = '#7C7468';
const FONT_STACK  = 'system-ui, -apple-system, "Segoe UI", sans-serif';

// ── Defaults globales de Chart.js ─────────────────────────────────────────────
Chart.defaults.font.family   = FONT_STACK;
Chart.defaults.font.size     = 12;
Chart.defaults.color         = TICK_COLOR;
Chart.defaults.plugins.legend.labels.boxWidth          = 10;
Chart.defaults.plugins.legend.labels.boxHeight         = 10;
Chart.defaults.plugins.legend.labels.padding           = 16;
Chart.defaults.plugins.legend.labels.usePointStyle     = true;

// ── Helpers ───────────────────────────────────────────────────────────────────
function scaleX() {
    return { grid: { display: false }, ticks: { color: TICK_COLOR } };
}
function scaleY(prefix = '') {
    return {
        grid: { color: GRID_COLOR, drawBorder: false },
        ticks: {
            color: TICK_COLOR,
            precision: 0,
            callback: v => prefix ? prefix + ' ' + v.toLocaleString('es-PE') : v,
        },
        beginAtZero: true,
    };
}

// ── Constructores de gráficas ─────────────────────────────────────────────────

function buildBar(id, labels, values, color, yPrefix = '') {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: color + 'BF',
                borderColor: color,
                borderWidth: 1.5,
                borderRadius: 4,
                borderSkipped: 'bottom',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: c => yPrefix
                            ? ` ${yPrefix} ${c.parsed.y.toLocaleString('es-PE')}`
                            : ` ${c.parsed.y}`,
                    },
                },
            },
            scales: { x: scaleX(), y: scaleY(yPrefix) },
        },
    });
}

function buildLine(id, labels, values, color) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: color,
                backgroundColor: color + '1A',
                borderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: color,
                tension: 0.35,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: scaleX(), y: scaleY() },
        },
    });
}

function buildDoughnut(id, labels, values, colors) {
    const ctx = document.getElementById(id);
    if (!ctx || !labels.length) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length).map(c => c + 'CC'),
                borderColor:     colors.slice(0, labels.length),
                borderWidth: 1.5,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { display: true, position: 'bottom' },
            },
        },
    });
}

function buildHBar(id, labels, values, color) {
    const ctx = document.getElementById(id);
    if (!ctx || !labels.length) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: color + 'BF',
                borderColor: color,
                borderWidth: 1.5,
                borderRadius: 4,
                borderSkipped: 'left',
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { color: GRID_COLOR, drawBorder: false },
                    ticks: { color: TICK_COLOR },
                    beginAtZero: true,
                },
                y: { grid: { display: false }, ticks: { color: TICK_COLOR } },
            },
        },
    });
}

// ── Placeholder cuando no hay datos ──────────────────────────────────────────
function showEmpty(id) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    const wrapper = ctx.closest('.chart-wrap');
    if (wrapper) {
        wrapper.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted);font-size:.82rem;">Sin datos aún</div>';
    }
}

// ── Init ──────────────────────────────────────────────────────────────────────
function tryChart(id, fn) {
    try { fn(); } catch (e) { console.error('Chart error [' + id + ']:', e); showEmpty(id); }
}

function initDashboard() {
    const D = window.CHART_DATA || {};
    const meses = D.meses || [];

    tryChart('chartCotizMes', () => {
        if (D.cotizacionesPorMes?.some(v => v > 0))
            buildBar('chartCotizMes', meses, D.cotizacionesPorMes, PALETA.s1);
        else showEmpty('chartCotizMes');
    });

    tryChart('chartEstadoCotiz', () => {
        if (D.estadoCotizaciones?.values?.length)
            buildDoughnut('chartEstadoCotiz',
                D.estadoCotizaciones.labels, D.estadoCotizaciones.values,
                [PALETA.s2, PALETA.s1, PALETA.s6, PALETA.s3, PALETA.s4]);
        else showEmpty('chartEstadoCotiz');
    });

    tryChart('chartContratosMes', () => {
        if (D.contratosPorMes?.some(v => v > 0))
            buildLine('chartContratosMes', meses, D.contratosPorMes, PALETA.s2);
        else showEmpty('chartContratosMes');
    });

    tryChart('chartEstadoContratos', () => {
        if (D.estadoContratos?.values?.length)
            buildDoughnut('chartEstadoContratos',
                D.estadoContratos.labels, D.estadoContratos.values,
                [PALETA.s1, PALETA.s4, PALETA.s6]);
        else showEmpty('chartEstadoContratos');
    });

    tryChart('chartSesionesMes', () => {
        if (D.sesionesPorMes?.some(v => v > 0))
            buildLine('chartSesionesMes', meses, D.sesionesPorMes, PALETA.s3);
        else showEmpty('chartSesionesMes');
    });

    tryChart('chartEstadoSesiones', () => {
        if (D.estadoSesiones?.values?.length)
            buildDoughnut('chartEstadoSesiones',
                D.estadoSesiones.labels, D.estadoSesiones.values,
                [PALETA.s3, PALETA.s4, PALETA.s6]);
        else showEmpty('chartEstadoSesiones');
    });

    tryChart('chartProductos', () => {
        if (D.productosMasVendidos?.values?.length)
            buildHBar('chartProductos',
                D.productosMasVendidos.labels, D.productosMasVendidos.values,
                PALETA.s5);
        else showEmpty('chartProductos');
    });

    tryChart('chartInstitucion', () => {
        if (D.valorPorInstitucion?.values?.length)
            buildBar('chartInstitucion',
                D.valorPorInstitucion.labels, D.valorPorInstitucion.values,
                PALETA.s1, 'S/');
        else showEmpty('chartInstitucion');
    });
}

document.addEventListener('DOMContentLoaded', initDashboard);
