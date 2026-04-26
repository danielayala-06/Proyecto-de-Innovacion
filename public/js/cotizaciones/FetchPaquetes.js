async function fetchServicios(){
    //
    const res = await fetch(`${BASE_URL}/servicios`);

    if(res.status !== 200) return;

    let data = await res.json();

    console.log(data)
    return data;
}