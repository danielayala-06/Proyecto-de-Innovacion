import { http } from '../utils/http.js';

export const colegioApi = {
    listar:    ()        => http.get('api/colegios'),
    obtener:   (id)      => http.get(`api/colegios/${id}`),
    actualizar:(id, data)=> http.put(`api/colegios/${id}`, data),
};
