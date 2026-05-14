// ─── Formulario nueva/editar sesión ──────────────────────────────────────────

export const sesionForm = {
    limpiar(tipo = '') {
        document.getElementById('sfId').value             = '';
        document.getElementById('sfTipo').value           = tipo;
        document.getElementById('sfFecha').value          = '';
        document.getElementById('sfHora').value           = '';
        document.getElementById('sfObservaciones').value  = '';
    },

    poblar(sesion) {
        document.getElementById('sfId').value = sesion.id_sesion;
        document.getElementById('sfTipo').value = sesion.tipo;
        const [fecha, hora] = sesion.fecha_hora_sesion.split(' ');
        document.getElementById('sfFecha').value         = fecha;
        document.getElementById('sfHora').value          = hora?.slice(0, 5) ?? '';
        document.getElementById('sfObservaciones').value = sesion.observaciones ?? '';
    },

    datos() {
        const fecha = document.getElementById('sfFecha').value;
        const hora  = document.getElementById('sfHora').value || '08:00';
        return {
            id_promocion:      parseInt(document.getElementById('sfPromocion').value, 10),
            tipo:              document.getElementById('sfTipo').value,
            fecha_hora_sesion: `${fecha} ${hora}:00`,
            observaciones:     document.getElementById('sfObservaciones').value.trim() || null,
        };
    },

    validar() {
        const fecha = document.getElementById('sfFecha').value;
        const tipo  = document.getElementById('sfTipo').value;
        if (!tipo)  return 'Selecciona el tipo de sesión.';
        if (!fecha) return 'La fecha es obligatoria.';
        return null;
    },

    getId() {
        return parseInt(document.getElementById('sfId').value, 10) || null;
    },
};

// ─── Formulario nuevo estudiante ──────────────────────────────────────────────

export const estudianteForm = {
    limpiar() {
        ['efNombres','efApellidos','efNacimiento','efColor','efProfesion',
         'apNombres','apApellidos','apTelefono','apDocTipo','apDocNum',
         'apCorreo','apRelacion'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    },

    datos(idPromocion) {
        return {
            id_promocion: idPromocion,
            estudiante: {
                nombres:          document.getElementById('efNombres').value.trim(),
                apellidos:        document.getElementById('efApellidos').value.trim(),
                fecha_nacimiento: document.getElementById('efNacimiento').value || null,
                color_fav:        document.getElementById('efColor').value.trim()     || null,
                profesion_futura: document.getElementById('efProfesion').value.trim() || null,
            },
            apoderado: {
                nombres:          document.getElementById('apNombres').value.trim(),
                apellidos:        document.getElementById('apApellidos').value.trim() || null,
                telefono:         document.getElementById('apTelefono').value.trim(),
                tipo_documento:   document.getElementById('apDocTipo').value,
                numero_documento: document.getElementById('apDocNum').value.trim(),
                correo:           document.getElementById('apCorreo').value.trim()  || null,
                tipo_relacion:    document.getElementById('apRelacion').value,
            },
        };
    },

    validar() {
        const reqs = {
            efNombres:  'El nombre del estudiante es obligatorio.',
            efApellidos:'Los apellidos del estudiante son obligatorios.',
            apNombres:  'El nombre del apoderado es obligatorio.',
            apTelefono: 'El teléfono del apoderado es obligatorio.',
            apDocTipo:  'El tipo de documento del apoderado es obligatorio.',
            apDocNum:   'El número de documento del apoderado es obligatorio.',
            apRelacion: 'La relación con el estudiante es obligatoria.',
        };
        for (const [id, msg] of Object.entries(reqs)) {
            if (!document.getElementById(id)?.value.trim()) return msg;
        }
        const tel = document.getElementById('apTelefono').value.trim();
        if (!/^\d{9}$/.test(tel)) return 'El teléfono debe tener exactamente 9 dígitos.';
        return null;
    },
};
