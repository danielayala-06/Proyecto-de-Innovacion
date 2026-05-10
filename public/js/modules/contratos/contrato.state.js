export const state = {
  filas:     [],
  filtradas: [],
  pagina:    1,
  porPagina: 10,
  sortKey:   null,
  sortDir:   'asc',

  todasCotizaciones:      [],
  cotizacionSeleccionada: null,
};

export function calcularStats(filas) {
  return filas.reduce(
    (acc, c) => {
      acc.total++;
      acc.monto_total += parseFloat(c.total) || 0;
      const e = c.estado?.toUpperCase();
      if (e === 'ACTIVO')     acc.vigentes++;
      if (e === 'COMPLETADO') acc.completados++;
      return acc;
    },
    { total: 0, vigentes: 0, completados: 0, monto_total: 0 }
  );
}
