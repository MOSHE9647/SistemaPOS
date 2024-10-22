import { mostrarMensaje } from "../../gui/notification.js";
import { renderTable } from "./gui.js";

// ********************************************************************************** //
// ************* Constantes y variables predefinidas para la paginación ************* //
// ********************************************************************************** //

// Constantes
const DEFAULT_PAGE_SIZE = 5; // Tamaño de página predeterminado

// Variables globales en el objeto window
window.sort = 'nombre';
window.totalRecords = 0;
window.currentPage = 1;
window.totalPages = 1;
window.pageSize = DEFAULT_PAGE_SIZE;

// ************************************************************************** //
// ************* Métodos para manipulación dinámica de la tabla ************* //
// ************************************************************************** //

/**
 * Obtiene la lista de categorías desde el servidor.
 * 
 * @description Esta función realiza una solicitud GET al servidor para obtener la lista de categorías,
 *              procesa la respuesta y actualiza la tabla de categorías en la página.
 * 
 * @param {number} page - El número de página a obtener
 * @param {number} size - El tamaño de la página (número de registros por página)
 * @param {string} sort - El campo por el que ordenar la lista de categorías
 * 
 * @example
 * fetchCategorias(1, 10, 'nombre');
 * 
 * @returns {void}
 */
export async function fetchCategorias(page, size, sort) {
    try {
        // Realizar solicitud GET al servidor para obtener la lista de categorías
        const response = await fetch(
            `${window.baseURL}/controller/categoriaAction.php?page=${page}&size=${size}&sort=${sort}`
        );
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al obtener las categorías');
            return;
        }

        // Renderizar la tabla de categorías con los datos obtenidos
        renderTable(data.categorias);

        // Actualizar variables de paginación
        window.currentPage = data.page;
        window.totalPages = data.totalPages;
        window.totalRecords = data.totalRecords;
        window.pageSize = data.size;

        // Actualizar controles de paginación
        updatePaginationControls();
    } catch (error) {
        // Muestra el mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al obtener la lista de categorías. ${error}`, 'error', 'Error interno');
    }
}

/**
 * Actualiza los controles de paginación en la página.
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
 * Cambia la página actual en la lista de categorías.
 * 
 * @description Esta función verifica si la nueva página es válida (entre 1 y el total de páginas)
 *              y, si es así, llama a la función fetchCategorias para obtener la lista de categorías
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
        // Llamar a la función fetchCategorias para obtener la lista de categorías de la nueva página
        fetchCategorias(newPage, window.pageSize, window.sort);
    }
}

/**
 * Cambia el tamaño de página actual en la lista de categorías.
 * 
 * @description Esta función actualiza el tamaño de página y llama a la función fetchCategorias
 *              para obtener la lista de categorías correspondiente a la página actual con el nuevo tamaño.
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
    // Llamar a la función fetchCategorias para obtener la lista de categorías con el nuevo tamaño
    fetchCategorias(window.currentPage, window.pageSize, window.sort);
}

/**
 * Cambia el orden de la lista de categorías.
 * 
 * @description Esta función actualiza el orden de la lista y llama a la función fetchCategorias
 *              para obtener la lista de categorías correspondiente a la página actual con el nuevo orden.
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
    // Llamar a la función fetchCategorias para obtener la lista de categorías con el nuevo orden
    fetchCategorias(window.currentPage, window.pageSize, window.sort);
}