const base = () => (window.BASE_URL || '/').replace(/\/$/, '/');

const jsonHeaders = () => ({
  'Content-Type': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
});

async function request(method, endpoint, body = null, params = {}) {
  let url = base() + endpoint;

  const filteredParams = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  if (Object.keys(filteredParams).length) {
    url += '?' + new URLSearchParams(filteredParams).toString();
  }

  const opts = { method, headers: jsonHeaders() };
  if (body !== null) opts.body = JSON.stringify(body);

  const res = await fetch(url, opts);
  const json = await res.json().catch(() => ({ message: `HTTP ${res.status}` }));

  if (!res.ok) {
    let message = json.message;
    if (!message && json.errors) {
      message = Object.values(json.errors).flat().join(' | ');
    }
    const err = new Error(message || `Error ${res.status}`);
    err.status = res.status;
    err.data   = json;
    throw err;
  }

  return json;
}

export const http = {
  get:    (endpoint, params = {}) => request('GET',    endpoint, null, params),
  post:   (endpoint, body)        => request('POST',   endpoint, body),
  put:    (endpoint, body)        => request('PUT',    endpoint, body),
  patch:  (endpoint, body)        => request('PATCH',  endpoint, body),
  delete: (endpoint)              => request('DELETE', endpoint),
};
