import { http } from '../utils/http.js';

export const cotizacionApi = {
  listar:        (params = {}) => http.get('api/cotizaciones', params),
  obtener:       (id)          => http.get(`api/cotizaciones/${id}`),
  crear:         (data)        => http.post('api/cotizaciones', data),
  actualizar:    (id, data)    => http.put(`api/cotizaciones/${id}`, data),
  cambiarEstado: (id, estado)  => http.patch(`api/cotizaciones/${id}/estado`, { estado }),
  eliminar:      (id)          => http.delete(`api/cotizaciones/${id}`),
};
