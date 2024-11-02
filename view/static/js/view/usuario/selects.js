// ********************************************************************************** //
// ************* Métodos para obtener la lista de roles para un usuario ************* //
// ********************************************************************************** //

import { mostrarMensaje } from "../../gui/notification.js";
import { verificarRespuestaJSON } from "../../utils.js";

/**
 * Carga la lista de roles desde el servidor.
 * 
 * Esta función envía una solicitud GET al servidor para obtener la lista de roles.
 * Si la solicitud es exitosa, devuelve los datos en formato JSON. En caso de error,
 * muestra un mensaje de error y devuelve un objeto vacío.
 * 
 * @async
 * @function
 * @returns {Promise<Object>} Los datos de los roles en formato JSON.
 */
async function loadRoles() {
    try {
        // Enviar la solicitud GET al servidor para obtener los roles con los datos en la URL
        const response = await fetch(`${window.baseURL}/controller/rolUsuarioAction.php?accion=obtener`);
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        return await verificarRespuestaJSON(response);
    } catch (error) {
        console.error(`Ocurrió un error al obtener la lista de roles: ${error}`);
        mostrarMensaje(`Ocurrió un error al obtener la lista de roles.<br>${error}`, 'error', 'Error');
        return {};
    }
}

/**
 * Inicializa los selects de roles.
 * 
 * Esta función carga los roles utilizando la función `loadRoles` y luego
 * llama a `loadSelectRoles` para poblar el select de roles con los datos obtenidos.
 * 
 * @function
 */
export function initializeSelects() {
    loadRoles().then(data => {
        // Asignar los datos a una variable global
        if (data) {
            window.dataR = data;
            loadSelectRoles();
        }
    });
}

/**
 * Pobla el elemento select de roles con opciones.
 * 
 * Esta función limpia cualquier opción existente en el select con id 'rol-select',
 * y luego lo puebla con roles del array global `window.dataR.roles`.
 * Cada rol se añade como un elemento option con su ID como valor y su nombre
 * como contenido de texto. La opción se selecciona si coincide con el valor
 * anterior del select o si el ID del rol es 1.
 * 
 * @function
 */
function loadSelectRoles() {
    const rolesSelect = document.getElementById('rol-select');
    let value = rolesSelect.value;
    rolesSelect.innerHTML = ''; // Limpiar opciones anteriores

    if (window.dataR.roles) {
        window.dataR.roles.forEach(rol => {
            const option = document.createElement('option');
            option.value = rol.ID;
            option.textContent = rol.Nombre;
            option.selected = option.value === value || rol.ID === 1;
            rolesSelect.appendChild(option);
        });
    } else {
        console.error('No se pudo cargar la lista de roles. No hay datos disponibles.');
        mostrarMensaje('No se pudo cargar la lista de roles.', 'error', 'Error');
    }
}