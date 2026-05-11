import { http } from '../utils/http.js';

export const clienteApi = {
    listar:    ()      => http.get('api/clientes'),
    obtener:   (id)    => http.get(`api/clientes/${id}`),
    crear:     (data)  => http.post('api/clientes', data),
    reniecDni: (dni)   => http.get('api/reniec/dni', { numero: dni }),
};
