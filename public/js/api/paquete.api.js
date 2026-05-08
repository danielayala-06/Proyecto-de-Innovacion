import { http } from '../utils/http.js';

export const paqueteApi = {
  listar: (params = {}) => http.get('api/paquetes', params),
};
