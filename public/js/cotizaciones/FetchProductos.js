document.addEventListener("DOMContentLoaded", ()=>{
})

async function fetchProductos(){
    //
    const res = await fetch(`${BASE_URL}/productos`);

    if(res.status !== 200) return;

    let data = await res.json();

    console.log(data)
    return data;
}