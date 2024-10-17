import { mostrarMensaje } from "../../gui/notification.js";
import { renderTable } from "./gui.js";

// ********************************************************************************** //
// ************* Constantes y variables predefinidas para la paginación ************* //
// ********************************************************************************** //

// Constantes
const DEFAULT_PAGE_SIZE = 5; // Tamaño de página predeterminado

// Variables globales en el objeto window
window.search = '';
window.sort = 'email';
window.totalRecords = 0;
window.currentPage = 1;
window.totalPages = 1;
window.pageSize = DEFAULT_PAGE_SIZE;

// ************************************************************************** //
// ************* Métodos para manipulación dinámica de la tabla ************* //
// ************************************************************************** //

/**
 * Obtiene la lista de usuarios desde el servidor.
 * 
 * @description Esta función realiza una solicitud GET al servidor para obtener la lista de usuarios,
 *              procesa la respuesta y actualiza la tabla de usuarios en la página.
 * 
 * @param {number} page - El número de página a obtener
 * @param {number} size - El tamaño de la página (número de registros por página)
 * @param {string} sort - El campo por el que ordenar la lista de usuarios
 * @param {string} [search=''] - El valor de búsqueda para filtrar la lista de usuarios
 * 
 * @example
 * fetchUsuarios(1, 10, 'tipo');
 * 
 * @returns {void}
 */
export async function fetchUsuarios(page, size, sort, search = '') {
    try {
        // Realizar solicitud GET al servidor para obtener la lista de usuarios
        const response = await fetch(
            `${window.baseURL}/controller/usuarioAction.php?page=${page}&size=${size}&sort=${sort}&search=${search}`
        );
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al obtener los clientes');
            return;
        }

        // Actualizar las variables globales de paginación
        window.currentPage = data.page;
        window.pageSize = data.pageSize;
        window.totalPages = data.totalPages;
        window.totalRecords = data.totalRecords;

        // Actualizar la tabla de usuarios en la página
        renderTable(data.usuarios);

        // Actualizar los controles de paginación
        updatePaginationControls();
    } catch (error) {
        // Mostrar mensaje de error si la solicitud falla
        mostrarMensaje(`Ocurrió un error al obtener la lista de usuarios.<br>${error}`, 'error', 'Error al obtener los usuarios');
    }
}

/**
 * Actualiza los contusuarios de paginación en la página.
 * 
 * @description Esta función actualiza los valores de texto y estado de los botones de paginación
 *              en función de los valores actuales de página, registros totales y páginas totales.
 * 
 * @example
 * updatePaginationControls();
 * 
 * @returns {void}
 */
export function updatePaginationControls() {
    // Actualizar el texto del total de registros
    document.getElementById('totalRecords').textContent = window.totalRecords;

    // Actualizar el texto del número de página actual
    document.getElementById('currentPage').textContent = window.currentPage;

    // Actualizar el texto del total de páginas
    document.getElementById('totalPages').textContent = window.totalPages;

    // Desactivar el botón de página anterior si estamos en la primera página
    document.getElementById('prevPage').disabled = window.currentPage === 1;
    document.getElementById('prevPage').classList.toggle('disabled', window.currentPage === 1);

    // Desactivar el botón de página siguiente si estamos en la última página
    document.getElementById('nextPage').disabled = window.currentPage === window.totalPages;
    document.getElementById('nextPage').classList.toggle('disabled', window.currentPage === window.totalPages);
}

/**
 * Cambia la página actual en la lista de usuarios.
 * 
 * @description Esta función verifica si la nueva página es válida (entre 1 y el total de páginas)
 *              y, si es así, llama a la función fetchUsuarios para obtener la lista de usuarios
 *              correspondiente a la nueva página.
 * 
 * @param {number} newPage - El número de la nueva página a mostrar
 * 
 * @example
 * changePage(3);
 * 
 * @returns {void}
 */
export function changePage(newPage) {
    // Verificar si la nueva página es válida (entre 1 y el total de páginas)
    if (newPage >= 1 && newPage <= window.totalPages) {
        // Llamar a la función fetchUsuarios para obtener la lista de usuarios de la nueva página
        fetchUsuarios(newPage, window.pageSize, window.sort);
    }
}

/**
 * Cambia el tamaño de página actual en la lista de usuarios.
 * 
 * @description Esta función actualiza el tamaño de página y llama a la función fetchUsuarios
 *              para obtener la lista de usuarios correspondiente a la página actual con el nuevo tamaño.
 * 
 * @param {number} newSize - El nuevo tamaño de página
 * 
 * @example
 * changePageSize(10);
 * 
 * @returns {void}
 */
export function changePageSize(newSize) {
    // Actualizar el tamaño de página
    window.pageSize = newSize;
    // Llamar a la función fetchUsuarios para obtener la lista de usuarios con el nuevo tamaño
    fetchUsuarios(window.currentPage, window.pageSize, window.sort);
}

/**
 * Cambia el orden de la lista de usuarios.
 * 
 * @description Esta función actualiza el orden de la lista y llama a la función fetchUsuarios
 *              para obtener la lista de usuarios correspondiente a la página actual con el nuevo orden.
 * 
 * @param {string} newSort - El nuevo orden de la lista (nombre_del_campo)
 * 
 * @example
 * changePageSort('nombre');
 * 
 * @returns {void}
 */
export function changePageSort(newSort) {
    // Actualizar el orden de la lista
    window.sort = newSort;
    // Llamar a la función fetchUsuarios para obtener la lista de usuarios con el nuevo orden
    fetchUsuarios(window.currentPage, window.pageSize, window.sort);
}

/**
 * Busca usuarios utilizando el valor del input de búsqueda.
 * 
 * @description Obtiene el valor del input con id 'usuario-search-input' y llama a la función fetchUsuarios
 *              con los parámetros de página, tamaño de página, orden y el valor de búsqueda.
 */
export function searchUsuarios() {
    // Obtener el valor del input de búsqueda
    const search = document.getElementById('usuario-search-input').value;
    window.search = search; // Actualizar el valor de búsqueda
    fetchUsuarios(1, window.pageSize, window.sort, search);
}