import { http } from '../utils/http.js';

export const sesionApi = {
    listar:            (params = {})       => http.get('api/sesiones', params),
    obtener:           (id)                => http.get(`api/sesiones/${id}`),
    crear:             (data)              => http.post('api/sesiones', data),
    actualizar:        (id, data)          => http.put(`api/sesiones/${id}`, data),
    cambiarEstado:     (id, estado)        => http.patch(`api/sesiones/${id}/estado`, { estado }),
    limite:            (idPromocion, tipo) => http.get(`api/sesiones/limite/${idPromocion}`, { tipo }),
    agregarEstudiante: (id, idEstudiante)  => http.post(`api/sesiones/${id}/asistencia`, { id_estudiante: idEstudiante }),
    quitarEstudiante:  (id, idEstudiante)  => http.delete(`api/sesiones/${id}/asistencia/${idEstudiante}`),
    marcarAsistencia:  (id, idEstudiante, asistio) =>
        http.patch(`api/sesiones/${id}/asistencia/${idEstudiante}`, { asistio }),
};
