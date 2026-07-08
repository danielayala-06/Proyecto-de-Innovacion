// Dashboard module — Ronceros Fotografía
// Datos simulados. Para conectar a la API, reemplaza cada bloque DATA.*
// con: fetch(BASE_URL + 'index.php/api/dashboard/<recurso>')

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

// ── Datos simulados ───────────────────────────────────────────────────────────
const MESES = ['Ago', 'Sep', 'Oct', 'Nov', 'Dic', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul'];

const DATA = {
    cotizacionesPorMes: {
        labels: MESES,
        values: [3, 5, 7, 4, 9, 6, 8, 5, 7, 4, 6, 7],
    },
    estadoCotizaciones: {
        labels: ['Pendiente', 'Aprobada', 'Rechazada', 'Expirada'],
        values: [3, 2, 1, 1],
    },
    contratosPorMes: {
        labels: MESES,
        values: [2, 3, 5, 3, 6, 4, 5, 4, 5, 3, 4, 5],
    },
    estadoContratos: {
        labels: ['Activo', 'Completado', 'Cancelado'],
        values: [5, 3, 1],
    },
    sesionesPorMes: {
        labels: MESES,
        values: [4, 6, 8, 5, 10, 7, 9, 6, 8, 5, 7, 8],
    },
    estadoSesiones: {
        labels: ['Pendiente', 'Finalizado', 'Cancelado'],
        values: [2, 8, 1],
    },
    productosMasVendidos: {
        labels: ['Cuadros 40×50', 'Cuadros 30×40', 'Anuario Digital', 'Álbum Familiar', 'Mini álbum'],
        values: [42, 38, 25, 19, 12],
    },
    valorPorInstitucion: {
        labels: ['I.E. San José', 'Colegio Divina Gracia', 'I.E. Maristas', 'Colegio Fé y Alegría'],
        values: [8400, 6300, 5100, 3800],
    },
};

// ── Defaults globales de Chart.js ─────────────────────────────────────────────
Chart.defaults.font.family   = FONT_STACK;
Chart.defaults.font.size     = 12;
Chart.defaults.color         = TICK_COLOR;
Chart.defaults.plugins.legend.labels.boxWidth = 12;
Chart.defaults.plugins.legend.labels.padding  = 16;
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.pointStyleWidth = 10;

// ── Helpers ───────────────────────────────────────────────────────────────────
function scaleX() {
    return { grid: { display: false }, ticks: { color: TICK_COLOR } };
}
function scaleY(prefix = '') {
    return {
        grid: { color: GRID_COLOR, drawBorder: false },
        ticks: {
            color: TICK_COLOR,
            callback: v => prefix ? prefix + v.toLocaleString('es-PE') : v,
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
                backgroundColor: color + 'BF', // 75%
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
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors.map(c => c + 'CC'),
                borderColor:     colors,
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

// ── Init ──────────────────────────────────────────────────────────────────────
function initDashboard() {
    buildBar('chartCotizMes',
        DATA.cotizacionesPorMes.labels,
        DATA.cotizacionesPorMes.values,
        PALETA.s1);

    buildDoughnut('chartEstadoCotiz',
        DATA.estadoCotizaciones.labels,
        DATA.estadoCotizaciones.values,
        [PALETA.s1, PALETA.s2, PALETA.s6, PALETA.s3]);

    buildLine('chartContratosMes',
        DATA.contratosPorMes.labels,
        DATA.contratosPorMes.values,
        PALETA.s2);

    buildDoughnut('chartEstadoContratos',
        DATA.estadoContratos.labels,
        DATA.estadoContratos.values,
        [PALETA.s1, PALETA.s4, PALETA.s6]);

    buildLine('chartSesionesMes',
        DATA.sesionesPorMes.labels,
        DATA.sesionesPorMes.values,
        PALETA.s3);

    buildDoughnut('chartEstadoSesiones',
        DATA.estadoSesiones.labels,
        DATA.estadoSesiones.values,
        [PALETA.s3, PALETA.s4, PALETA.s6]);

    buildHBar('chartProductos',
        DATA.productosMasVendidos.labels,
        DATA.productosMasVendidos.values,
        PALETA.s5);

    buildBar('chartInstitucion',
        DATA.valorPorInstitucion.labels,
        DATA.valorPorInstitucion.values,
        PALETA.s1, 'S/');
}

document.addEventListener('DOMContentLoaded', initDashboard);
