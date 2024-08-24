// ********************************************************************************** //
// ************* Constantes y variables predefinidas para la paginación ************* //
// ********************************************************************************** //

// Constantes
const DEFAULT_PAGE_SIZE = 5; // Tamaño de página predeterminado

// Variables globales
let sort = 'nombre';
let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
let pageSize = DEFAULT_PAGE_SIZE;

// ************************************************************************** //
// ************* Métodos para manipulación dinámica de la tabla ************* //
// ************************************************************************** //

/**
 * Obtiene la lista de productos desde el servidor.
 * 
 * @description Esta función realiza una solicitud GET al servidor para obtener la lista de productos,
 *              procesa la respuesta y actualiza la tabla de productos en la página.
 * 
 * @param {number} page - El número de página a obtener
 * @param {number} size - El tamaño de la página (número de registros por página)
 * @param {string} sort - El campo por el que ordenar la lista de productos
 * 
 * @example
 * fetchProductos(1, 10, 'nombre');
 * 
 * @returns {void}
 */
function fetchProductos(page, size, sort) {
    // Realizar solicitud GET al servidor para obtener la lista de productos
    fetch(`../controller/productoAction.php?page=${page}&size=${size}&sort=${sort}`)
        .then(response => response.json())
        .then(data => {
            // Verificar si la respuesta fue exitosa
            if (data.success) {
                // Renderizar la tabla de productos con los datos obtenidos
                renderTable(data.listaProductos);

                // Actualizar variables de paginación
                currentPage = data.page;
                totalPages = data.totalPages;
                totalRecords = data.totalRecords;
                pageSize = data.size;

                // Actualizar controles de paginación
                updatePaginationControls();
            } else {
                // Muestra el mensaje de error específico enviado desde el servidor
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Muestra el mensaje de error detallado
            showMessage(`Ocurrió un error al obtener la lista de productos.<br>${error}`, 'error');
        });
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
function updatePaginationControls() {
    // Actualizar el texto del total de registros
    document.getElementById('totalRecords').textContent = totalRecords;

    // Actualizar el texto del número de página actual
    document.getElementById('currentPage').textContent = currentPage;

    // Actualizar el texto del total de páginas
    document.getElementById('totalPages').textContent = totalPages;

    // Desactivar el botón de página anterior si estamos en la primera página
    document.getElementById('prevPage').disabled = currentPage === 1;

    // Desactivar el botón de página siguiente si estamos en la última página
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

/**
 * Cambia la página actual en la lista de productos.
 * 
 * @description Esta función verifica si la nueva página es válida (entre 1 y el total de páginas)
 *              y, si es así, llama a la función fetchProductos para obtener la lista de productos
 *              correspondiente a la nueva página.
 * 
 * @param {number} newPage - El número de la nueva página a mostrar
 * 
 * @example
 * changePage(3);
 * 
 * @returns {void}
 */
function changePage(newPage) {
    // Verificar si la nueva página es válida (entre 1 y el total de páginas)
    if (newPage >= 1 && newPage <= totalPages) {
        // Llamar a la función fetchProductos para obtener la lista de productos de la nueva página
        fetchProductos(newPage, pageSize, sort);
    }
}

/**
 * Cambia el tamaño de página actual en la lista de productos.
 * 
 * @description Esta función actualiza el tamaño de página y llama a la función fetchProductos
 *              para obtener la lista de productos correspondiente a la página actual con el nuevo tamaño.
 * 
 * @param {number} newSize - El nuevo tamaño de página
 * 
 * @example
 * changePageSize(10);
 * 
 * @returns {void}
 */
function changePageSize(newSize) {
    // Actualizar el tamaño de página
    pageSize = newSize;
    // Llamar a la función fetchProductos para obtener la lista de productos con el nuevo tamaño
    fetchProductos(currentPage, pageSize, sort);
}

/**
 * Cambia el orden de la lista de productos.
 * 
 * @description Esta función actualiza el orden de la lista y llama a la función fetchProductos
 *              para obtener la lista de productos correspondiente a la página actual con el nuevo orden.
 * 
 * @param {string} newSort - El nuevo orden de la lista (nombre_del_campo)
 * 
 * @example
 * changePageSort('nombre');
 * 
 * @returns {void}
 */
function changePageSort(newSort) {
    // Actualizar el orden de la lista
    sort = newSort;
    // Llamar a la función fetchproductos para obtener la lista de productos con el nuevo orden
    fetchProductos(currentPage, pageSize, sort);
}

// Agregar evento de cambio al selector de tamaño de página
document.getElementById('pageSizeSelector').addEventListener('change', (event) => {
    // Llamar a la función changePageSize con el nuevo tamaño de página seleccionado
    changePageSize(event.target.value);
});

// Agregar evento de cambio al selector de orden
document.getElementById('sortSelector').addEventListener('change', (event) => {
    // Llamar a la función changePageSort con el nuevo orden seleccionado
    changePageSort(event.target.value);
});

// ************************************************************************* //
// ************* Llamada inicial para cargar la primera página ************* //
// ************************************************************************* //

fetchProductos(currentPage, pageSize, sort);