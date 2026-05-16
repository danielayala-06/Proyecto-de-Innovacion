/**
 * @file    cliente.api.js
 * @module  api/clienteApi
 *
 * Capa de acceso a la API REST para el recurso Clientes.
 * Encapsula todas las llamadas HTTP relacionadas con clientes y
 * la consulta al RENIEC vía el proxy interno de la aplicación.
 *
 * Endpoints consumidos:
 *  - GET  api/clientes        → listar todos los clientes
 *  - GET  api/clientes/{id}   → detalle de un cliente
 *  - POST api/clientes        → crear un nuevo cliente
 *  - GET  api/reniec/dni      → consulta DNI al RENIEC (proxy Decolecta)
 */

import { http } from '../utils/http.js';

/**
 * API de clientes y consulta RENIEC.
 *
 * @namespace clienteApi
 * @property {function(): Promise<Object>}           listar    - Obtiene la lista completa de clientes.
 * @property {function(number): Promise<Object>}     obtener   - Obtiene el detalle de un cliente por ID.
 * @property {function(Object): Promise<Object>}     crear     - Crea un nuevo cliente.
 * @property {function(string): Promise<Object>}     reniecDni - Consulta datos de una persona por DNI al RENIEC.
 */
export const clienteApi = {
    /**
     * @param {number} id - ID del cliente.
     * @returns {Promise<Object>} Respuesta de la API con `data: Cliente`.
     */
    listar:    ()      => http.get('api/clientes'),
    obtener:   (id)    => http.get(`api/clientes/${id}`),
    crear:     (data)  => http.post('api/clientes', data),

    /**
     * Consulta el RENIEC a través del proxy interno (Decolecta).
     *
     * @param {string} dni - Número de DNI de 8 dígitos.
     * @returns {Promise<Object>} Respuesta con `data: { nombres, apellidos }`.
     */
    reniecDni: (dni)   => http.get('api/reniec/dni', { numero: dni }),
};
