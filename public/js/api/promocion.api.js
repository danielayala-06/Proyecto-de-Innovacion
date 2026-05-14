import { http } from '../utils/http.js';

export const promocionApi = {
  listar:  (params = {}) => http.get('api/promociones', params),
  obtener: (id)          => http.get(`api/promociones/${id}`),
};
