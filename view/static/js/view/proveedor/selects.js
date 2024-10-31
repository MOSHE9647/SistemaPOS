// *********************************************************************** //
// ************* Métodos para obtener la lista de Categorias ************* //
// *********************************************************************** //

import { mostrarMensaje } from "../../gui/notification.js";
import { verificarRespuestaJSON } from "../../utils.js";

/**
 * Obtiene la lista de categorías desde el servidor.
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de categorías.
 */
async function loadCategorias() {
    try {
        const response = await fetch(`${window.baseURL}/controller/categoriaAction.php?accion=all`);
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al obtener las categorías');
        return await verificarRespuestaJSON(response);
    } catch (error) {
        // Muestra un mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al obtener las categorías.<br>${error}`, 'error');
        return {};
    }
}

/**
 * Llena el elemento select de categorías con opciones.
 */
function loadSelectCategorias() {
    const categoriasSelect = document.getElementById('categoria-select');
    if (!categoriasSelect) console.error('No se encontró el select de categorías');
    let value = categoriasSelect.value;
    categoriasSelect.innerHTML = ''; // Limpia las opciones anteriores

    // Asegura que `window.dataCategorias` esté disponible antes de usarlo
    if (window.dataCategorias.categorias) {
        window.dataCategorias.categorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.ID;
            option.textContent = categoria.Nombre;
            option.selected = option.value === value;
            categoriasSelect.appendChild(option);
        });
    }
}

/**
 * Inicializa los elementos select cargando datos desde el servidor y llenando las opciones.
 */
export async function initializeSelects() {
    if (window.dataCategorias) delete window.dataCategorias;

    try {
        // Carga y asigna categorías
        window.dataCategorias = await loadCategorias();
        loadSelectCategorias();
    } catch (error) {
        mostrarMensaje(`Ocurrió un error al inicializar el select de categorias.<br>${error}`, 'error');
    }
}
