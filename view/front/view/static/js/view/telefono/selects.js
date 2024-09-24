// *************************************************************************************************************** //
// ************* Métodos para obtener las listas de Proveedores, Tipos de Teléfono y Códigos de País ************* //
// *************************************************************************************************************** //

import { mostrarMensaje } from "../../gui/notification.js";

/**
 * Carga la lista de tipos de teléfono desde un archivo JSON.
 * 
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de tipos de teléfono.
 * 
 * @example
 * loadTipos().then(data => {
 *   console.log(data);
 * });
 */
async function loadTipos() {
    try {
        const response = await fetch('../front/view/static/json/telefono/tipos.json');
        return await response.json();
    } catch (error) {
        // Muestra el mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al obtener la lista de tipos de teléfono.<br>${error}`, 'error', 'Error de carga');
        return {};
    }
}

/**
 * Carga la lista de códigos de país desde un archivo JSON.
 * 
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de códigos de país.
 * 
 * @example
 * loadCodigosPais().then(data => {
 *   console.log(data);
 * });
 */
async function loadCodigosPais() {
    try {
        const response = await fetch('../front/view/static/json/telefono/codigos.json');
        return await response.json();
    } catch (error) {
        // Muestra el mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al obtener la lista de códigos de país.<br>${error}`, 'error', 'Error de carga');
        return {};
    }
}

/**
 * Inicializa los select de proveedores, tipos de teléfono y códigos de país.
 */
export function initializeSelects() {
    loadTipos().then(data => {
        // Asignar los datos a una variable global
        window.dataT = data;
        loadSelectTipos();
    });
    loadCodigosPais().then(data => {
        // Asignar los datos a una variable global
        window.dataC = data;
        loadSelectCodigosPais();
    });
}

/**
 * Carga las opciones del select de tipos de teléfono.
 */
function loadSelectTipos() {
    const tiposSelect = document.getElementById('tipo-select');
    let value = tiposSelect.value;
    tiposSelect.innerHTML = ''; // Limpiar opciones anteriores

    // Asegura que `window.dataT` esté disponible antes de usarlo
    if (window.dataT.tipos) {
        window.dataT.tipos.forEach(tipo => {
            const option = document.createElement('option');
            option.value = tipo;
            option.textContent = tipo;
            option.selected = option.value === value;
            tiposSelect.appendChild(option);
        });
    }
}

/**
 * Carga las opciones del select de códigos de país.
 */
function loadSelectCodigosPais() {
    const codigosSelect = document.getElementById('codigo-select');
    let value = codigosSelect.value;
    codigosSelect.innerHTML = ''; // Limpiar opciones anteriores

    // Asegura que `window.dataC` esté disponible antes de usarlo
    if (window.dataC.codigos) {
        window.dataC.codigos.forEach(codigo => {
            const option = document.createElement('option');
            option.value = codigo.codigo;
            option.textContent = codigo.pais;
            option.selected = option.value === value;
            codigosSelect.appendChild(option);
        });
    }
}