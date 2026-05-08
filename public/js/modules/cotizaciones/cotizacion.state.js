export const state = {
  filas:     [],   // lista completa recibida de la API
  filtradas: [],   // lista tras búsqueda/filtro activos
  pagina:    1,
  porPagina: 10,
  sortKey:   null,
  sortDir:   'asc',
};

/** Calcula los totales de las tarjetas de estadísticas */
export function calcularResumenes(filas) {
  return filas.reduce(
    (acc, c) => {
      acc.total++;
      acc.monto_total += parseFloat(c.total) || 0;
      const e = c.estado?.toUpperCase();
      if (e === 'BORRADOR' || e === 'PENDIENTE') acc.borrador++;
      else if (e === 'APROBADA')  acc.aprobadas++;
      else if (e === 'RECHAZADA') acc.rechazadas++;
      return acc;
    },
    { total: 0, borrador: 0, aprobadas: 0, rechazadas: 0, monto_total: 0 }
  );
}
