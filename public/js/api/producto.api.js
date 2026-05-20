/**
 * @file    producto.api.js
 * @module  api/productoApi
 *
 * Capa de acceso a la API REST para el recurso Productos.
 *
 * Endpoints consumidos:
 *  - GET api/productos → lista simplificada de productos activos
 */

import { http } from '../utils/http.js';

export const productoApi = {
    listar: () => http.get('api/productos'),
};
