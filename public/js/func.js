
// ELEMENTOS DEL DOM 
const menu = document.getElementById('menu');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');
const modalOverlay = document.getElementById('modal-overlay');
const btnNuevaCotizacion = document.getElementById('btn-nueva-cotizacion');
const btnCancelar = document.getElementById('btn-cancelar');
const modalClose = document.getElementById('modal-close');
const btnGuardar = document.getElementById('btn-guardar');
const listaCotizaciones = document.getElementById('lista-cotizaciones');
const filtroCount = document.getElementById('filtro-count');

//  TOGGLE 
menu.addEventListener('click', () => {
    sidebar.classList.toggle('menu-toggle');
    menu.classList.toggle('menu-toggle');
    mainContent.classList.toggle('sidebar-open');
});

//  NAVEGACION ENTRE SECCIONES 
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const seccion = link.dataset.section;

        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('selected'));
        link.classList.add('selected');

        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        const target = document.getElementById('section-' + seccion);
        if (target) target.classList.add('active');
    });
});

// ALMACENAMIENTO LOCAL 
const STORAGE_KEY = 'ronceros_cotizaciones';

function cargarCotizaciones() {
    const data = localStorage.getItem(STORAGE_KEY);
    return data ? JSON.parse(data) : [];
}

function guardarCotizaciones(cotizaciones) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cotizaciones));
}

// ESTADO DE LA APP
let cotizaciones = cargarCotizaciones();
let filtroActivo = 'todas';

// VIGENCIA 
const DIAS_VIGENCIA = 7;

function calcularVigencia(fechaCreacion) {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const creacion = new Date(fechaCreacion);
    creacion.setHours(0, 0, 0, 0);
    const vencimiento = new Date(creacion);
    vencimiento.setDate(vencimiento.getDate() + DIAS_VIGENCIA);
    const diasRestantes = Math.ceil((vencimiento - hoy) / (1000 * 60 * 60 * 24));
    return { diasRestantes, vencimiento };
}

function esVigente(fechaCreacion) {
    return calcularVigencia(fechaCreacion).diasRestantes > 0;
}

// FORMATEAR FECHA
function formatFecha(isoDate) {
    if (!isoDate) return 'â€”';
    const [y, m, d] = isoDate.split('-');
    return `${d}/${m}/${y}`;
}

function formatFechaCorta(dateObj) {
    const d = dateObj.getDate().toString().padStart(2, '0');
    const m = (dateObj.getMonth() + 1).toString().padStart(2, '0');
    const y = dateObj.getFullYear();
    return `${d}/${m}/${y}`;
}

//  RENDERIZAR LISTA 
function renderLista() {
    const filtradas = cotizaciones.filter(c => {
        if (filtroActivo === 'vigente') return esVigente(c.fechaCreacion);
        if (filtroActivo === 'caducada') return !esVigente(c.fechaCreacion);
        return true;
    });

    // Actualizar contador
    const totales = {
        todas: cotizaciones.length,
        vigente: cotizaciones.filter(c => esVigente(c.fechaCreacion)).length,
        caducada: cotizaciones.filter(c => !esVigente(c.fechaCreacion)).length
    };
    filtroCount.textContent = `${filtradas.length} cotizaciÃ³n${filtradas.length !== 1 ? 'es' : ''}`;

    // Limpiar lista (mantener empty state en DOM)
    const emptyState = document.getElementById('empty-state');
    listaCotizaciones.innerHTML = '';
    listaCotizaciones.appendChild(emptyState);

    if (filtradas.length === 0) {
        emptyState.style.display = 'flex';
        if (filtroActivo !== 'todas') {
            emptyState.querySelector('p').textContent = `No hay cotizaciones ${filtroActivo === 'vigente' ? 'vigentes' : 'caducadas'}`;
            emptyState.querySelector('span').textContent = '';
        } else {
            emptyState.querySelector('p').textContent = 'No hay cotizaciones registradas';
            emptyState.querySelector('span').textContent = 'Crea una nueva cotizaciÃ³n para comenzar';
        }
        return;
    }

    emptyState.style.display = 'none';

    // Ordenar: MAS RECIENTES
    const ordenadas = [...filtradas].sort((a, b) => new Date(b.fechaCreacion) - new Date(a.fechaCreacion));

    ordenadas.forEach(c => {
        const vigente = esVigente(c.fechaCreacion);
        const { diasRestantes, vencimiento } = calcularVigencia(c.fechaCreacion);

        let textoVigencia;
        if (!vigente) {
            textoVigencia = `VenciÃ³ el ${formatFechaCorta(vencimiento)}`;
        } else if (diasRestantes === 1) {
            textoVigencia = 'Vence hoy';
        } else {
            textoVigencia = `Vence en ${diasRestantes} dÃ­as`;
        }

        const precio = parseFloat(c.precio).toLocaleString('es-PE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        const card = document.createElement('div');
        card.className = 'cotizacion-card';
        card.dataset.id = c.id;
        card.innerHTML = `
            <div class="card-info">
                <div class="card-dni">DNI: ${c.dni}</div>
                <div class="card-nombre">${c.nombres}</div>
                <div class="card-meta">
                    <span class="card-meta-item">
                        <i class="fa-solid fa-calendar"></i>
                        ${formatFecha(c.fechaEvento)}
                    </span>
                    <span class="card-meta-item">
                        <i class="fa-solid fa-clock"></i>
                        ${c.horaEvento || 'â€”'}
                    </span>
                    <span class="card-meta-item">
                        <i class="fa-solid fa-calendar-plus"></i>
                        Creada el ${formatFecha(c.fechaCreacion)}
                    </span>
                </div>
            </div>
            <div class="card-right">
                <div class="card-precio">S/ ${precio}</div>
                <span class="card-badge ${vigente ? 'badge-vigente' : 'badge-caducada'}">
                    <i class="fa-solid fa-circle" style="font-size:0.5rem"></i>
                    ${vigente ? 'Vigente' : 'Caducada'}
                </span>
                <div class="card-vigencia">${textoVigencia}</div>
            </div>
            <button class="btn-eliminar" data-id="${c.id}" title="Eliminar cotizaciÃ³n">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;
        listaCotizaciones.appendChild(card);
    });

    // Botones eliminar
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            if (confirm('Eliminar cotizacion?')) {
                cotizaciones = cotizaciones.filter(c => c.id !== id);
                guardarCotizaciones(cotizaciones);
                renderLista();
            }
        });
    });
}

// FILTROS 
document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filtroActivo = btn.dataset.filtro;
        renderLista();
    });
});

function abrirModal() {
    limpiarFormulario();
    modalOverlay.classList.add('active');
    document.getElementById('dni').focus();
}

function cerrarModal() {
    modalOverlay.classList.remove('active');
}

btnNuevaCotizacion.addEventListener('click', abrirModal);
btnCancelar.addEventListener('click', cerrarModal);
modalClose.addEventListener('click', cerrarModal);
modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) cerrarModal();
});

// Cerrar con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
        cerrarModal();
    }
});


function mostrarError(campo, mensaje) {
    const input = document.getElementById(campo);
    const error = document.getElementById('error-' + campo);
    if (input) input.classList.add('error');
    if (error) error.textContent = mensaje;
}

function limpiarError(campo) {
    const input = document.getElementById(campo);
    const error = document.getElementById('error-' + campo);
    if (input) input.classList.remove('error');
    if (error) error.textContent = '';
}

function limpiarFormulario() {
    ['dni', 'nombres', 'fecha-evento', 'hora-evento', 'precio'].forEach(campo => {
        const input = document.getElementById(campo);
        if (input) input.value = '';
        limpiarError(campo.replace('-', '').replace('evento', ''));
    });
    // limpiar errores manualmente tambiÃ©n
    ['dni', 'nombres', 'fecha', 'hora', 'precio'].forEach(c => limpiarError(c));
    document.getElementById('dni').classList.remove('error');
    document.getElementById('nombres').classList.remove('error');
    document.getElementById('fecha-evento').classList.remove('error');
    document.getElementById('hora-evento').classList.remove('error');
    document.getElementById('precio').classList.remove('error');
}

function validarFormulario() {
    let valido = true;

    const dni = document.getElementById('dni').value.trim();
    const nombres = document.getElementById('nombres').value.trim();
    const fechaEvento = document.getElementById('fecha-evento').value;
    const horaEvento = document.getElementById('hora-evento').value;
    const precio = document.getElementById('precio').value;

    limpiarError('dni');
    limpiarError('nombres');
    limpiarError('fecha');
    limpiarError('hora');
    limpiarError('precio');

    if (!dni) {
        mostrarError('dni', 'El DNI es requerido');
        valido = false;
    } else if (!/^\d{8}$/.test(dni)) {
        mostrarError('dni', 'El DNI debe tener 8 dÃ­gitos numÃ©ricos');
        valido = false;
    }

    if (!nombres) {
        mostrarError('nombres', 'Los nombres son requeridos');
        valido = false;
    } else if (nombres.length < 3) {
        mostrarError('nombres', 'Ingresa el nombre completo');
        valido = false;
    }

    if (!fechaEvento) {
        mostrarError('fecha', 'La fecha del evento es requerida');
        document.getElementById('fecha-evento').classList.add('error');
        valido = false;
    }

    if (!horaEvento) {
        mostrarError('hora', 'La hora del evento es requerida');
        document.getElementById('hora-evento').classList.add('error');
        valido = false;
    }

    if (!precio || isNaN(precio)) {
        mostrarError('precio', 'El precio es requerido');
        valido = false;
    } else if (parseFloat(precio) <= 0) {
        mostrarError('precio', 'El precio debe ser mayor a 0');
        valido = false;
    }

    return valido;
}

// GUARDAR COTIZALIÑO 
btnGuardar.addEventListener('click', () => {
    if (!validarFormulario()) return;

    const hoy = new Date();
    const fechaCreacion = hoy.toISOString().split('T')[0];

    const nueva = {
        id: Date.now().toString(),
        dni: document.getElementById('dni').value.trim(),
        nombres: document.getElementById('nombres').value.trim(),
        fechaEvento: document.getElementById('fecha-evento').value,
        horaEvento: document.getElementById('hora-evento').value,
        precio: document.getElementById('precio').value,
        fechaCreacion: fechaCreacion
    };

    cotizaciones.push(nueva);
    guardarCotizaciones(cotizaciones);

    cerrarModal();

    // Cambiar filtro para ver la nueva cotizacion
    filtroActivo = 'todas';
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('[data-filtro="todas"]').classList.add('active');

    renderLista();
});

// VALIDACION EN TIEMPO REAL 
document.getElementById('dni').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 8);
    limpiarError('dni');
});
document.getElementById('nombres').addEventListener('input', () => limpiarError('nombres'));
document.getElementById('fecha-evento').addEventListener('change', () => {
    limpiarError('fecha');
    document.getElementById('fecha-evento').classList.remove('error');
});
document.getElementById('hora-evento').addEventListener('change', () => {
    limpiarError('hora');
    document.getElementById('hora-evento').classList.remove('error');
});
document.getElementById('precio').addEventListener('input', () => limpiarError('precio'));

// INICIALIZAR 
renderLista();
