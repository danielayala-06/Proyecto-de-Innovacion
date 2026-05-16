/**
 * @file    alerts.js
 * @module  utils/alerts
 *
 * Sistema de notificaciones toast para la interfaz.
 * Las notificaciones se muestran en la esquina inferior derecha, se apilan
 * automáticamente y se autodestruyen a los 4,5 segundos.
 *
 * Los estilos se inyectan dinámicamente en el DOM la primera vez que se llama
 * a cualquier función de este módulo (no requieren CSS externo).
 *
 * @example
 * import { alerts } from '../utils/alerts.js';
 * alerts.ok('Guardado correctamente.');
 * alerts.error('No se pudo conectar con el servidor.');
 */

/** @type {Object<string,string>} Colores por tipo de notificación (referencias a CSS custom properties). */
const COLORES = {
  success: 'var(--green-text,  #4caf50)',
  error:   'var(--red-text,    #e57373)',
  warning: 'var(--amber-text,  #ffa726)',
  info:    'var(--accent,      #7c9cbf)',
};

/** @type {Object<string,string>} Clases de icono Bootstrap Icons por tipo de notificación. */
const ICONOS = {
  success: 'bi-check-circle-fill',
  error:   'bi-x-circle-fill',
  warning: 'bi-exclamation-triangle-fill',
  info:    'bi-info-circle-fill',
};

/**
 * Inyecta el bloque de estilos CSS del toast en el <head> del documento.
 * Solo se ejecuta una vez; las llamadas posteriores son no-op.
 *
 * @returns {void}
 */
function inyectarEstilo() {
  if (document.getElementById('_toast-css')) return;
  const s = document.createElement('style');
  s.id = '_toast-css';
  s.textContent = `
    @keyframes _toastIn {from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
    ._toast{
      position:fixed;bottom:1.25rem;right:1.25rem;z-index:9999;
      background:var(--card-bg,#fff);border:1px solid var(--border-color,#ddd);
      border-radius:10px;padding:.7rem 1rem;min-width:240px;max-width:340px;
      box-shadow:0 4px 18px rgba(0,0,0,.18);display:flex;align-items:center;gap:.55rem;
      font-size:.84rem;color:var(--text-primary,#222);
      animation:_toastIn .22s ease;
    }
    ._toast + ._toast{margin-bottom:.5rem;}
  `;
  document.head.appendChild(s);
}

/**
 * Crea y muestra un toast con el mensaje e icono correspondiente al tipo.
 * Se elimina del DOM automáticamente tras 4 500 ms.
 *
 * @param {string} mensaje - Texto a mostrar en la notificación.
 * @param {'success'|'error'|'warning'|'info'} [tipo='info'] - Tipo de notificación.
 * @returns {void}
 */
function mostrar(mensaje, tipo = 'info') {
  inyectarEstilo();
  const div = document.createElement('div');
  div.className = '_toast';
  div.innerHTML = `
    <i class="bi ${ICONOS[tipo]}" style="font-size:1.05rem;color:${COLORES[tipo]};flex-shrink:0;"></i>
    <span style="flex:1;">${mensaje}</span>
    <button onclick="this.parentElement.remove()"
      style="background:none;border:none;cursor:pointer;color:var(--text-muted,#888);font-size:.85rem;padding:0;line-height:1;">✕</button>
  `;
  document.body.appendChild(div);
  setTimeout(() => div.remove(), 4500);
}

/**
 * Interfaz pública del sistema de notificaciones toast.
 *
 * @namespace alerts
 * @property {function(string): void} ok      - Muestra una notificación de éxito (verde).
 * @property {function(string): void} error   - Muestra una notificación de error (rojo).
 * @property {function(string): void} warning - Muestra una notificación de advertencia (naranja).
 * @property {function(string): void} info    - Muestra una notificación informativa (azul).
 */
export const alerts = {
  ok:      (msg) => mostrar(msg, 'success'),
  error:   (msg) => mostrar(msg, 'error'),
  warning: (msg) => mostrar(msg, 'warning'),
  info:    (msg) => mostrar(msg, 'info'),
};
