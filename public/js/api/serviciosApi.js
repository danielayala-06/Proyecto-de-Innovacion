export async function fetchServicios(){
    //
    const res = await fetch(`${BASE_URL}/api/servicios`);

    if(res.status !== 200) return;

    return await res.json();
}