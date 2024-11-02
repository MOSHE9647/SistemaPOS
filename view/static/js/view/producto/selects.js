// ***************************************************************************************************************** //
// ************* Métodos para obtener las listas de Categorias, Subcategorías, Marcas y Presentaciones ************* //
// ***************************************************************************************************************** //

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
 * Obtiene la lista de subcategorías para un ID de categoría dado desde el servidor.
 * @param {number} categoriaID - El ID de la categoría.
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de subcategorías.
 */
async function loadSubcategorias(categoriaID) {
    try {
        const response = await fetch(`${window.baseURL}/controller/subcategoriaAction.php?accion=categoria&id=${categoriaID}`);
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al obtener las subcategorías');
        return await verificarRespuestaJSON(response);
    } catch (error) {
        // Muestra un mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al obtener la lista de subcategorías.<br>${error}`, 'error');
        return {};
    }
}

/**
 * Obtiene la lista de marcas desde el servidor.
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de marcas.
 */
async function loadMarcas() {
    try {
        const response = await fetch(`${window.baseURL}/controller/marcaAction.php?accion=all`);
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al obtener las marcas');
        return await verificarRespuestaJSON(response);
    } catch (error) {
        // Muestra un mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al obtener las marcas.<br>${error}`, 'error');
        return {};
    }
}

/**
 * Obtiene la lista de presentaciones desde el servidor.
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de presentaciones.
 */
async function loadPresentaciones() {
    try {
        const response = await fetch(`${window.baseURL}/controller/presentacionAction.php?accion=all`);
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al obtener las presentaciones');
        return await verificarRespuestaJSON(response);
    } catch (error) {
        // Muestra un mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al obtener las presentaciones.<br>${error}`, 'error');
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
 * Llena el elemento select de subcategorías con opciones.
 */
function loadSelectSubcategorias() {
    const subcategoriasSelect = document.getElementById('subcategoria-select');
    let value = subcategoriasSelect.value;
    subcategoriasSelect.innerHTML = ''; // Limpia las opciones anteriores

    // Asegura que `window.dataSubcategorias` esté disponible antes de usarlo
    if (window.dataSubcategorias.subcategorias) {
        window.dataSubcategorias.subcategorias.forEach(subcategoria => {
            const option = document.createElement('option');
            option.value = subcategoria.ID;
            option.textContent = subcategoria.Nombre;
            option.selected = option.value === value;
            subcategoriasSelect.appendChild(option);
        });
    }
}

/**
 * Llena el elemento select de marcas con opciones.
 */
function loadSelectMarcas() {
    const marcasSelect = document.getElementById('marca-select');
    let value = marcasSelect.value;
    marcasSelect.innerHTML = ''; // Limpia las opciones anteriores

    // Asegura que `window.dataMarcas` esté disponible antes de usarlo
    if (window.dataMarcas.marcas) {
        window.dataMarcas.marcas.forEach(marca => {
            const option = document.createElement('option');
            option.value = marca.ID;
            option.textContent = marca.Nombre;
            option.selected = option.value === value;
            marcasSelect.appendChild(option);
        });
    }
}

/**
 * Llena el elemento select de presentaciones con opciones.
 */
function loadSelectPresentaciones() {
    const presentacionesSelect = document.getElementById('presentacion-select');
    let value = presentacionesSelect.value;
    presentacionesSelect.innerHTML = ''; // Limpia las opciones anteriores

    // Asegura que `window.dataPresentaciones` esté disponible antes de usarlo
    if (window.dataPresentaciones.presentaciones) {
        window.dataPresentaciones.presentaciones.forEach(presentacion => {
            const option = document.createElement('option');
            option.value = presentacion.ID;
            option.textContent = presentacion.Nombre;
            option.selected = option.value === value;
            presentacionesSelect.appendChild(option);
        });
    }
}

/**
 * Limpia los datos almacenados en el objeto global window.
 */
function clearWindowData() {
    if (window.dataCategorias)      delete window.dataCategorias;
    if (window.dataSubcategorias)   delete window.dataSubcategorias;
    if (window.dataMarcas)          delete window.dataMarcas;
    if (window.dataPresentaciones)  delete window.dataPresentaciones;
}

/**
 * Inicializa los elementos select cargando datos desde el servidor y llenando las opciones.
 */
export async function initializeSelects() {
    clearWindowData(); // Limpia las variables de datos en window

    try {
        // Carga y asigna categorías
        window.dataCategorias = await loadCategorias();
        loadSelectCategorias();

        // Agrega evento de cambio para cargar subcategorías
        const categoriasSelect = document.getElementById('categoria-select');
        categoriasSelect.addEventListener('change', async function() {
            const categoriaID = categoriasSelect.value;
            if (categoriaID) {
                window.dataSubcategorias = await loadSubcategorias(categoriaID);
                loadSelectSubcategorias();
            }
        });

        // Carga y asigna marcas
        window.dataMarcas = await loadMarcas();
        loadSelectMarcas();

        // Carga y asigna presentaciones
        window.dataPresentaciones = await loadPresentaciones();
        loadSelectPresentaciones();
    } catch (error) {
        mostrarMensaje(`Ocurrió un error al inicializar los selects.<br>${error}`, 'error');
    }
}
