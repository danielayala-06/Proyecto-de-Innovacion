import { http } from '../utils/http.js';

export const clienteApi = {
  listar: () => http.get('api/clientes'),
};
