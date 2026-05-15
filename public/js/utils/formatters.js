const MESES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

export const formatters = {
  moneda(valor, moneda = 'PEN') {
    const simbolos = { PEN: 'S/', USD: '$', EUR: '€' };
    const s = simbolos[moneda] || moneda;
    return `${s} ${parseFloat(valor || 0).toFixed(2)}`;
  },

  fecha(str) {
    if (!str) return '—';
    const d = new Date(str + (str.includes('T') ? '' : 'T00:00:00'));
    if (isNaN(d)) return str;
    return `${String(d.getDate()).padStart(2,'0')} ${MESES[d.getMonth()]} ${d.getFullYear()}`;
  },

  estado(e) {
    const map = {
      PENDIENTE:         'Pendiente',
      APROBADA:          'Aprobada',
      RECHAZADA:         'Rechazada',
      EXPIRADA:          'Expirada',
      CONTRATO_GENERADO: 'Contrato generado',
    };
    return map[e?.toUpperCase()] ?? (e || '—');
  },

  codigo(id) {
    return '#' + String(id).padStart(4, '0');
  },
};
