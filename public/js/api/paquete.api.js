import { http } from '../utils/http.js';

export const paqueteApi = {
    listar:        (params = {}) => http.get('api/paquetes', params),
    obtener:       (id)          => http.get(`api/paquetes/${id}`),
    crear:         (data)        => http.post('api/paquetes', data),
    actualizar:    (id, data)    => http.put(`api/paquetes/${id}`, data),
    cambiarEstado: (id, estado)  => http.patch(`api/paquetes/${id}/estado`, { estado }),
};
