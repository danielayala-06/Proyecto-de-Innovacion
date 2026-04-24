/*document.addEventListener("DOMContentLoaded", function (){
    // Obtenemos la data del cliente a buscar(nombre, dni, telefono)
    const search_cliente = document.getElementById("searchCliente").value
    const clientes =  sendRequest(76982415)
    console.log(clientes)
})*/

document.addEventListener("DOMContentLoaded", async function (){

    // Obtenemos referencia a los input del cliente
    const input_nombre = document.querySelector('#nombreCliente')
    const input_dni = document.querySelector('#dniCliente')
    const input_telefono = document.querySelector('#telefonoCliente')
    const input_correo = document.querySelector('#emailCliente')

    // Probamos la request
    const clientes = await sendRequest(76982415);
    console.log(clientes);
    console.log(`${BASE_URL}/searchCliente`);

});
//
function setDataCliente(data){
    // Verificamos que la data no este vacia:
    if(!data)return;

    // Obtenemos referencia a los input del cliente
    const input_nombre = document.querySelector('#nombreCliente')
    const input_dni = document.querySelector('#dniCliente')
    const input_telefono = document.querySelector('#telefonoCliente')
    const input_correo = document.querySelector('#emailCliente')

    // Insertamos los datos recividos al formulario.

}

/**
 * Esta funcion envia una solicitud al back-end para buscar al cliente por el (dni, telefono, nombres)
 *
 * @param input_value
 * @returns {Promise<any|string>}
 */
async function sendRequest(input_value){

    const data_search = detectarTipo(input_value);

    if(data_search === 'desconocido' || data_search === null){
        return 'Dato inválido';
    }

    try{
        const res = await fetch(`${BASE_URL}/searchCliente`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tipo: data_search,
                valor: input_value
            })
        });

        if (!res.ok) {
            console.error('Error en la respuesta');
            return res.json();
        }

        const data = await res.json();
        return data;

    }catch(e){
        console.error(e);
    }
}


// Identificamos y validamos los datos del tipo de campo que nos envio
function detectarTipo(input) {
    // Convertimos el input en String
    input = String(input).trim();

    // DNI: 8 dígitos
    if (/^\d{8}$/.test(input)) return 'numero_documento';

    // Teléfono: 9 dígitos
    if (/^\d{9}$/.test(input)) return 'telefono';

    // Nombre: letras, espacios y tildes
    if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(input)) return 'nombres';

    return 'desconocido';
}

