import { sesionApi }              from '../../api/sesion.api.js';
import { estudianteApi }           from '../../api/estudiante.api.js';
import { state }                   from './sesion.state.js';
import { ui }                      from './sesion.ui.js';
import { sesionForm, estudianteForm } from './sesion.form.js';
import { alerts }                  from '../../utils/alerts.js';

// Modales Bootstrap
let _modalSesion    = null;
let _modalEstudiante = null;
let _offcanvas      = null;

// ─── Inicialización ───────────────────────────────────────────────────────────

async function cargarPromocion(idPromocion) {
    state.activePromocion = idPromocion;

    // Sesiones
    try {
        const res = await sesionApi.listar({ id_promocion: idPromocion });
        state.sesiones[idPromocion] = res.data ?? [];
    } catch { state.sesiones[idPromocion] = []; }

    // Estudiantes
    try {
        const res = await estudianteApi.listar(idPromocion);
        state.estudiantes[idPromocion] = res.data ?? [];
    } catch { state.estudiantes[idPromocion] = []; }

    // Límites por tipo
    const tipos = [...new Set((CONFIG_SESIONES || []).map(c => c.tipo_sesion))];
    state.limites[idPromocion] = {};
    for (const tipo of tipos) {
        try {
            const r = await sesionApi.limite(idPromocion, tipo);
            state.limites[idPromocion][tipo] = r.data;
        } catch {
            state.limites[idPromocion][tipo] = { permitidas: 0, usadas: 0, puede_crear: false };
        }
    }

    ui.renderTabs(state.promociones, idPromocion);
    ui.renderSesiones(
        state.sesiones[idPromocion],
        state.limites[idPromocion],
        CONFIG_SESIONES || []
    );
    ui.renderEstudiantes(state.estudiantes[idPromocion], idPromocion);

    // Asignar eventos a botones "Nueva sesión" que renderizó ui
    document.querySelectorAll('.btn-add-sesion').forEach(btn => {
        btn.addEventListener('click', () => abrirNuevaSesion(btn.dataset.tipo));
    });
}

// ─── SESIONES ─────────────────────────────────────────────────────────────────

window.abrirNuevaSesion = function (tipo = '') {
    sesionForm.limpiar(tipo);
    document.getElementById('sfPromocion').value = state.activePromocion;
    document.getElementById('modalSesionTitulo').textContent = 'Nueva sesión';
    _modalSesion?.show();
};

window.abrirEditarSesion = async function (id) {
    try {
        const res = await sesionApi.obtener(id);
        sesionForm.poblar(res.data);
        document.getElementById('sfPromocion').value = state.activePromocion;
        document.getElementById('modalSesionTitulo').textContent = 'Editar sesión';
        _modalSesion?.show();
    } catch {
        alerts.error('No se pudo cargar la sesión.');
    }
};

window.guardarSesion = async function () {
    const err = sesionForm.validar();
    if (err) { alerts.error(err); return; }

    const datos = sesionForm.datos();
    const id    = sesionForm.getId();

    try {
        if (id) {
            await sesionApi.actualizar(id, datos);
            alerts.ok('Sesión actualizada.');
        } else {
            await sesionApi.crear(datos);
            alerts.ok('Sesión creada.');
        }
        _modalSesion?.hide();
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al guardar la sesión.');
    }
};

window.cambiarEstadoSesion = async function (id, estado) {
    try {
        await sesionApi.cambiarEstado(id, estado);
        alerts.ok(`Sesión marcada como ${estado}.`);
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al cambiar el estado.');
    }
};

// ─── ASISTENCIA (offcanvas) ───────────────────────────────────────────────────

window.abrirAsistencia = async function (idSesion) {
    try {
        const res = await sesionApi.obtener(idSesion);
        state.sesionActiva = res.data;
        const estudiantes  = state.estudiantes[state.activePromocion] ?? [];
        document.getElementById('asistenciaTitulo').textContent =
            `Asistencia · ${res.data.tipo} · ${res.data.fecha_hora_sesion.slice(0, 10)}`;
        ui.renderAsistencia(res.data, estudiantes);
        _offcanvas?.show();
    } catch {
        alerts.error('No se pudo cargar la sesión.');
    }
};

window.agregarAAsistencia = async function (idSesion, idEstudiante) {
    try {
        await sesionApi.agregarEstudiante(idSesion, idEstudiante);
        await abrirAsistencia(idSesion);
    } catch (e) {
        alerts.error(e.message || 'Error al agregar estudiante.');
    }
};

window.quitarDeAsistencia = async function (idSesion, idEstudiante) {
    try {
        await sesionApi.quitarEstudiante(idSesion, idEstudiante);
        await abrirAsistencia(idSesion);
    } catch (e) {
        alerts.error(e.message || 'Error al quitar estudiante.');
    }
};

window.marcarAsistencia = async function (idSesion, idEstudiante, valor) {
    try {
        await sesionApi.marcarAsistencia(idSesion, idEstudiante, valor);
        await abrirAsistencia(idSesion);
    } catch (e) {
        alerts.error(e.message || 'Error al marcar asistencia.');
    }
};

// ─── ESTUDIANTES ─────────────────────────────────────────────────────────────

window.abrirNuevoEstudiante = function (idPromocion) {
    estudianteForm.limpiar();
    state.activePromocion = idPromocion;
    _modalEstudiante?.show();
};

window.guardarEstudiante = async function () {
    const err = estudianteForm.validar();
    if (err) { alerts.error(err); return; }

    const datos = estudianteForm.datos(state.activePromocion);
    try {
        await estudianteApi.crear(datos);
        alerts.ok('Estudiante registrado.');
        _modalEstudiante?.hide();
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al registrar el estudiante.');
    }
};

window.eliminarEstudiante = async function (id) {
    if (!confirm('¿Eliminar este estudiante de la promoción? Esta acción no se puede deshacer.')) return;
    try {
        await estudianteApi.eliminar(id);
        alerts.ok('Estudiante eliminado.');
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al eliminar el estudiante.');
    }
};

// ─── INIT ─────────────────────────────────────────────────────────────────────

function init() {
    state.idContrato  = ID_CONTRATO;
    state.promociones = PROMOCIONES;

    _modalSesion     = new bootstrap.Modal(document.getElementById('modalSesion'));
    _modalEstudiante = new bootstrap.Modal(document.getElementById('modalEstudiante'));
    _offcanvas       = new bootstrap.Offcanvas(document.getElementById('offcanvasAsistencia'));

    // Tabs
    document.getElementById('promocionesTabs')?.addEventListener('click', e => {
        const btn = e.target.closest('.promo-tab');
        if (btn) cargarPromocion(parseInt(btn.dataset.id, 10));
    });

    if (state.promociones.length) {
        cargarPromocion(state.promociones[0].id_promocion);
    } else {
        ui.renderTabs([], null);
        document.getElementById('sesionesContainer').innerHTML = `
            <div class="empty-state">
                <i class="bi bi-mortarboard"></i>
                Este contrato no tiene promociones asociadas.
            </div>`;
    }
}

init();
