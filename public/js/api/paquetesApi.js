export async function fetchPaquetes(){

    try{

        const res = await fetch(`${BASE_URL}/api/paquetes`);

        // En caso de que haya sucedido un error
        if(res.status!== 200)return res.status

        return await res.json()

    }catch (error) {
        console.log(error)
        return null
    }
}