/**
 * @file    main.js
 * @module  main
 *
 * Punto de entrada principal del bundle JavaScript de la aplicación.
 * Actualmente importa únicamente el módulo de creación de cotizaciones,
 * que se auto-inicializa al ser cargado.
 *
 * Este archivo se referencia en las vistas que requieren el flujo de creación
 * de cotizaciones. Para otras páginas, cada vista carga su módulo directamente.
 */

import './modules/cotizaciones/cotizacionCrear.js';
