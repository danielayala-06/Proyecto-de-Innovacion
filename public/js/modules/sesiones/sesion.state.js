export const state = {
    idContrato:    null,
    promociones:   [],     // { id_promocion, nombre, ... }
    sesiones:      {},     // Map: id_promocion → sesion[]
    estudiantes:   {},     // Map: id_promocion → estudiante[]
    limites:       {},     // Map: `${id_promocion}_${tipo}` → { permitidas, usadas, puede_crear }
    sesionActiva:  null,   // sesion completa con asistencia para el panel de asistencia
};

export const TIPO_LABEL = {
    exteriores: 'Exteriores',
    colegio:    'Colegio',
    estudio:    'Estudio',
    otro:       'Otro',
};

export const TIPO_ICON = {
    exteriores: 'bi-tree',
    colegio:    'bi-building',
    estudio:    'bi-camera',
    otro:       'bi-geo-alt',
};

export const ESTADO_LABEL = {
    pendiente:  'Pendiente',
    finalizado: 'Finalizado',
    cancelado:  'Cancelado',
};

export const ESTADO_CLASS = {
    pendiente:  'badge-pendiente',
    finalizado: 'badge-aprobada',
    cancelado:  'badge-rechazada',
};

export function calcularStatsPromocion(sesiones) {
    return {
        total:      sesiones.length,
        pendientes: sesiones.filter(s => s.estado === 'pendiente').length,
        finalizadas: sesiones.filter(s => s.estado === 'finalizado').length,
    };
}
