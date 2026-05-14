import { http } from '../utils/http.js';

export const estudianteApi = {
    listar:    (idPromocion) => http.get('api/estudiantes', { id_promocion: idPromocion }),
    crear:     (data)        => http.post('api/estudiantes', data),
    actualizar:(id, data)    => http.put(`api/estudiantes/${id}`, data),
    eliminar:  (id)          => http.delete(`api/estudiantes/${id}`),
};
