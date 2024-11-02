// *************************************************************************************************************** //
// ************* Métodos para obtener las listas de Proveedores, Tipos de Teléfono y Códigos de País ************* //
// *************************************************************************************************************** //

import { mostrarMensaje } from "../../gui/notification.js";
import { verificarRespuestaJSON } from "../../utils.js";

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
        const response = await fetch(`${window.baseURL}/view/static/json/telefono/tipos.json`);
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        return await verificarRespuestaJSON(response);
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
        const response = await fetch(`${window.baseURL}/view/static/json/telefono/codigos.json`);
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        return await verificarRespuestaJSON(response);
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
        window.dataTipos = data;
        loadSelectTipos();
    });
    loadCodigosPais().then(data => {
        // Asignar los datos a una variable global
        window.dataCodigosPais = data;
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

    // Asegura que `window.dataTipos` esté disponible antes de usarlo
    if (window.dataTipos.tipos) {
        window.dataTipos.tipos.forEach(tipo => {
            const option = document.createElement('option');
            option.value = tipo;
            option.textContent = tipo;
            option.selected = option.value === value || tipo === 'Móvil';
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

    // Asegura que `window.dataCodigosPais` esté disponible antes de usarlo
    if (window.dataCodigosPais.codigos) {
        window.dataCodigosPais.codigos.forEach(codigo => {
            const option = document.createElement('option');
            option.value = codigo.codigo;
            option.textContent = codigo.pais;
            option.selected = option.value === value || codigo.codigo === '+506';
            codigosSelect.appendChild(option);
        });
    }
}