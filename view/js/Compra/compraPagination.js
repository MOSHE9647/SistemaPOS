// ********************************************************************************** //
// ************* Constantes y variables predefinidas para la paginación ************* //
// ********************************************************************************** //

// Constantes
const DEFAULT_PAGE_SIZE = 5; // Tamaño de página predeterminado

// Variables globales
let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
let pageSize = DEFAULT_PAGE_SIZE;
let proveedorID = 0; // ID del proveedor para filtrar

// ************************************************************************** //
// ************* Métodos para manipulación dinámica de la tabla ************* //
// ************************************************************************** //

/**
 * Obtiene la lista de compras desde el servidor para un proveedor específico.
 * 
 * @description Esta función realiza una solicitud GET al servidor para obtener la lista de compras,
 *              filtra por proveedor si se especifica y actualiza la tabla en la página.
 * 
 * @param {number} page - El número de página a obtener (índice 1).
 * @param {number} size - El tamaño de la página (número de registros por página).
 * @param {number} proveedorID - El ID del proveedor para filtrar las compras.
 * 
 * @example
 * fetchCompras(1, 10, 123);
 * 
 * @returns {void}
 */
function fetchCompras(page, size, proveedorID) {
    // Realizar solicitud GET al servidor para obtener la lista de compras
    fetch(`../controller/compraAction.php?page=${page}&size=${size}&proveedor=${proveedorID}`)
        .then(response => response.json())
        .then(data => {
            // Verificar si la respuesta fue exitosa
            if (data.success) {
                console.log(data); // Verifica que data contiene la información esperada
                // Renderizar la tabla de compras con los datos obtenidos
                renderTable(data.listaCompras);

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
            showMessage(`Ocurrió un error al obtener la lista de compras.<br>${error}`, 'error');
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
 * Cambia la página actual en la lista de compras.
 * 
 * @description Esta función verifica si la nueva página es válida (entre 1 y el total de páginas)
 *              y, si es así, llama a la función fetchCompras para obtener la lista de compras
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
        // Llamar a la función fetchCompras para obtener la lista de compras de la nueva página
        fetchCompras(newPage, pageSize, proveedorID);
    }
}

/**
 * Cambia el tamaño de página actual en la lista de compras.
 * 
 * @description Esta función actualiza el tamaño de página y llama a la función fetchCompras
 *              para obtener la lista de compras correspondiente a la página actual con el nuevo tamaño.
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
    // Llamar a la función fetchCompras para obtener la lista de compras con el nuevo tamaño
    fetchCompras(currentPage, pageSize, proveedorID);
}

// ************************************************************************* //
// ************* Llamada inicial para cargar la primera página ************* //
// ************************************************************************* //

fetchCompras(currentPage, pageSize, proveedorID);

