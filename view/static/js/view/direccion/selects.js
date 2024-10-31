// *********************************************************************************************** //
// ************* Métodos para obtener las listas de Provincias, Cantones y Distritos ************* //
// *********************************************************************************************** //

import { mostrarMensaje } from "../../gui/notification.js";
import { verificarRespuestaJSON } from "../../utils.js";

/**
 * Carga datos de un archivo JSON y devuelve un objeto JSON.
 * 
 * La función carga los datos de un archivo ubicado en '../view/js/direccion/datos.json'
 * y devuelve el objeto JSON parseado. Si ocurre un error durante la carga o parsing,
 * captura el error, lo registra en la consola y devuelve un objeto vacío.
 * 
 * @returns {object} El objeto JSON parseado del archivo.
 * 
 * @example
 * const datos = await loadSelects();
 * console.log(datos); // Salida: { provincias: [...], cantones: [...] }
 */
async function loadSelects() {
    try {
        const response = await fetch(`${window.baseURL}/view/static/json/direccion/datos.json`);
        return await verificarRespuestaJSON(response);
    } catch (error) {
        console.error('Error cargando datos JSON:', error);
        mostrarMensaje(`Error cargando los datos de las listas: ${error}`, 'error', 'Error');
        return {};
    }
}

/**
 * Limpia los datos almacenados en el objeto global window.
 */
function clearWindowData() {
    if (window.direccionData) delete window.direccionData;
}

/**
 * Inicializa los selects de provincia, cantón y distrito.
 * 
 * Carga los datos de las provincias, cantones y distritos desde un archivo JSON
 * y asigna los datos a una variable global. Luego, añade event listeners para
 * actualizar los selects dependientes cuando se selecciona una provincia o cantón.
 * 
 * @example
 * initializeSelects();
 */
export async function initializeSelects() {
    clearWindowData(); // Limpia las variables de datos en window

    try {
        window.direccionData = await loadSelects();
        loadProvincias();

        // Añadir event listeners para actualizar los selects dependientes
        const provinciaSelect = document.getElementById('provincia-select');
        if (!provinciaSelect) {
            mostrarMensaje('No se encontró el select de provincias.', 'error');
            return;
        }
        provinciaSelect.addEventListener('change', loadCantones);

        const cantonSelect = document.getElementById('canton-select');
        if (!cantonSelect) {
            mostrarMensaje('No se encontró el select de cantones.', 'error');
            return;
        }
        cantonSelect.addEventListener('change', loadDistritos);
    } catch (error) {
        mostrarMensaje(`Ocurrió un error al inicializar los selects.<br>${error}`, 'error');
    }
}

/**
 * Carga las provincias en el select de provincias.
 * 
 * Limpia las opciones anteriores y carga las provincias desde la variable global `window.direccionData`.
 * 
 * @example
 * loadProvincias();
 */
function loadProvincias() {
    const provinciaSelect = document.getElementById('provincia-select');
    let value = provinciaSelect.value;
    provinciaSelect.innerHTML = ''; // Limpiar opciones anteriores

    // Asegura que `window.direccionData` esté disponible antes de usarlo
    if (window.direccionData.provincias) {
        window.direccionData.provincias.forEach(provincia => {
            const option = document.createElement('option');
            option.dataset.field = provincia.id;
            option.value = provincia.nombre;
            option.textContent = provincia.nombre;
            option.selected = option.value.toLowerCase() === value.toLowerCase();
            provinciaSelect.appendChild(option);
        });
    }

    if (value !== null) {
        loadCantones();
    }
}

/**
 * Carga los cantones en el select de cantones según la provincia seleccionada.
 * 
 * Limpia las opciones anteriores y carga los cantones desde la variable global `window.direccionData` según la provincia seleccionada.
 * 
 * @example
 * loadCantones();
 */
function loadCantones() {
    const cantonSelect = document.getElementById('canton-select');
    let value = cantonSelect.value;
    cantonSelect.innerHTML = ''; // Limpiar opciones anteriores
    
    const provinciaSelect = document.getElementById('provincia-select');
    const provinciaIndex = (provinciaSelect.options[provinciaSelect.selectedIndex].dataset.field) - 1;

    if (provinciaIndex >= 0 && window.direccionData.provincias[provinciaIndex]) {
        window.direccionData.provincias[provinciaIndex].cantones.forEach(canton => {
            const option = document.createElement('option');
            option.dataset.field = canton.id;
            option.value = canton.nombre;
            option.textContent = canton.nombre;
            option.selected = option.value.toLowerCase() === value.toLowerCase();
            cantonSelect.appendChild(option);
        });
    }

    if (value !== null) {
        loadDistritos();
    }
}

/**
 * Carga los distritos en el select de distritos según la provincia y cantón seleccionados.
 * 
 * Limpia las opciones anteriores y carga los distritos desde la variable global `window.direccionData` según la provincia y cantón seleccionados.
 * 
 * @example
 * loadDistritos();
 */
function loadDistritos() {
    const distritoSelect = document.getElementById('distrito-select');
    let value = distritoSelect.value;
    distritoSelect.innerHTML = ''; // Limpiar opciones anteriores

    const provinciaSelect = document.getElementById('provincia-select');
    const provinciaIndex = (provinciaSelect.options[provinciaSelect.selectedIndex].dataset.field) - 1;

    const cantonSelect = document.getElementById('canton-select');
    const cantonIndex = (cantonSelect.options[cantonSelect.selectedIndex].dataset.field) - 1;

    if (cantonIndex >= 0 && provinciaIndex >= 0 && window.direccionData.provincias[provinciaIndex].cantones[cantonIndex]) {
        window.direccionData.provincias[provinciaIndex].cantones[cantonIndex].distritos.forEach(distrito => {
            const option = document.createElement('option');
            option.value = distrito.nombre;
            option.textContent = distrito.nombre;
            option.selected = option.value.toLowerCase() === value.toLowerCase();
            distritoSelect.appendChild(option);
        });
    }
}