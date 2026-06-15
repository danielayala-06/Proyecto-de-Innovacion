/**
 * @file    badges.js
 * @module  utils/badges
 *
 * Fábrica de resolvedores de badge por estado.
 * Cada dominio pasa su propio mapa estado→[clase, etiqueta]
 * y obtiene una función lista para usar en templates HTML.
 */

/**
 * Crea un resolvedor de badge para un mapa de estados dado.
 *
 * @param {Object<string, [string, string]>} map
 *   Objeto cuyas claves son estados EN MAYÚSCULAS y cuyos valores
 *   son tuplas `[clase-css, etiqueta-visible]`.
 * @returns {(estado: string|null|undefined) => string}
 *   Función que recibe un estado y devuelve el HTML `<span class="…">…</span>`.
 */
export function badgeResolver(map) {
  return (estado) => {
    const [cls, label] = map[estado?.toUpperCase()] ?? ['badge-inactivo', estado ?? '—'];
    return `<span class="${cls}">${label}</span>`;
  };
}
