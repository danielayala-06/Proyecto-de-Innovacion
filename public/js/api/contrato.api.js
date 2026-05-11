import { http } from '../utils/http.js';

export const contratoApi = {
  listar:        (params = {}) => http.get('api/contratos', params),
  obtener:       (id)          => http.get(`api/contratos/${id}`),
  crear:         (data)        => http.post('api/contratos', data),
  actualizar:    (id, data)    => http.patch(`api/contratos/${id}`, data),
  cambiarEstado: (id, estado)  => http.patch(`api/contratos/${id}/estado`, { estado }),
};
