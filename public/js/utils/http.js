/**
 * @file    http.js
 * @module  utils/http
 *
 * Cliente HTTP centralizado para consumir la API REST de la aplicación.
 *
 * Responsabilidades:
 *  - Prefija automáticamente las rutas con `window.BASE_URL` (declarado en cada vista).
 *  - Añade los headers `Content-Type: application/json` y `X-Requested-With: XMLHttpRequest`.
 *  - Filtra los parámetros de query con valor `null` o cadena vacía.
 *  - Normaliza los errores HTTP en instancias de `Error` con propiedades adicionales
 *    `status` (código HTTP) y `data` (body JSON de la respuesta).
 *  - Extrae el mensaje de error del body (`message` o primer valor de `errors`).
 *
 * @example
 * import { http } from '../utils/http.js';
 * const res = await http.get('api/clientes', { estado: 'ACTIVO' });
 * await http.post('api/clientes', { nombres: 'Juan', apellidos: 'Pérez' });
 */

/** @returns {string} BASE_URL normalizada (siempre termina en `/`). */
const base = () => (window.BASE_URL || '/').replace(/\/$/, '/');

/** @returns {Object<string,string>} Headers JSON estándar para la API. */
const jsonHeaders = () => ({
  'Content-Type': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
});

/**
 * Realiza una petición HTTP genérica y normaliza la respuesta / errores.
 *
 * @param {string}      method   - Método HTTP (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`).
 * @param {string}      endpoint - Ruta relativa de la API, p.ej. `'api/clientes'`.
 * @param {Object|null} [body=null]   - Payload a serializar como JSON (solo para métodos con cuerpo).
 * @param {Object}      [params={}]   - Parámetros de query string; se omiten los `null` y vacíos.
 * @returns {Promise<Object>} Objeto JSON de la respuesta cuando `res.ok === true`.
 * @throws {Error} Error con propiedades `status` (HTTP status code) y `data` (body de error).
 */
async function request(method, endpoint, body = null, params = {}) {
  let url = base() + endpoint;

  // Filtrar parámetros nulos o vacíos antes de construir el query string
  const filteredParams = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  if (Object.keys(filteredParams).length) {
    url += '?' + new URLSearchParams(filteredParams).toString();
  }

  const opts = { method, headers: jsonHeaders() };
  if (body !== null) opts.body = JSON.stringify(body);

  const res  = await fetch(url, opts);
  const json = await res.json().catch(() => ({ message: `HTTP ${res.status}` }));

  if (!res.ok) {
    let message = json.message;
    if (!message && json.errors) {
      message = Object.values(json.errors).flat().join(' | ');
    }
    const err    = new Error(message || `Error ${res.status}`);
    err.status   = res.status;
    err.data     = json;
    throw err;
  }

  return json;
}

/**
 * Interfaz pública del cliente HTTP.
 *
 * @namespace http
 * @property {function(string, Object=): Promise<Object>} get    - Petición GET con query params opcionales.
 * @property {function(string, Object): Promise<Object>}  post   - Petición POST con body JSON.
 * @property {function(string, Object): Promise<Object>}  put    - Petición PUT con body JSON.
 * @property {function(string, Object): Promise<Object>}  patch  - Petición PATCH con body JSON.
 * @property {function(string): Promise<Object>}          delete - Petición DELETE sin body.
 */
export const http = {
  get:    (endpoint, params = {}) => request('GET',    endpoint, null, params),
  post:   (endpoint, body)        => request('POST',   endpoint, body),
  put:    (endpoint, body)        => request('PUT',    endpoint, body),
  patch:  (endpoint, body)        => request('PATCH',  endpoint, body),
  delete: (endpoint)              => request('DELETE', endpoint),
};
