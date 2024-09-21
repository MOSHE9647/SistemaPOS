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
let loteID = 0; // ID del proveedor para filtrar
let compraID = 0; // ID del proveedor para filtrar
let productoID = 0; // ID del proveedor para filtrar

// ************************************************************************** //
// ************* Métodos para manipulación dinámica de la tabla ************* //
// ************************************************************************** //

/**
 * Obtiene una lista de detalles de compra del servidor y las renderiza en una tabla.
 * 
 * @param {number} page - El número de página a obtener (índice 1).
 * @param {number} size - El número de registros a obtener por página.
 * @param {number} loteID - El ID del proveedor para filtrar las compras.
 * @param {number} compraID - El ID del proveedor para filtrar las compras.
 * @param {number} productoID - El ID del proveedor para filtrar las compras.
 * 
 * @example
 * fetchCompraDetalles(1, 10); // Obtiene los primeros 10 registros.
 * 
 * @returns {undefined}
 */
function fetchCompraDetalles(page, size, loteID, compraID, productoID) {
    fetch(`../controller/compradetalleAction.php?page=${page}&size=${size}&lote=${loteID}&compra=${compraID}&producto=${productoID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(data); // Verifica que data contiene la información esperada
                renderTable(data.listaCompraDetalles); // Asegúrate de que renderTable renderiza correctamente

                currentPage = data.page;
                totalPages = data.totalPages;
                totalRecords = data.totalRecords;
                pageSize = data.size;

                updatePaginationControls();
            } else {
                // Muestra el mensaje de error específico enviado desde el servidor
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Muestra el mensaje de error detallado
            showMessage(`Ocurrió un error al obtener la lista de detalles de compra.<br>${error}`, 'error');
        });
}

/**
 * Actualiza los controles de paginación.
 * 
 * @description Actualiza los valores de los elementos de la interfaz de usuario que muestran la información de paginación.
 * @example
 * updatePaginationControls();
 */
function updatePaginationControls() {
    document.getElementById('totalRecords').textContent = totalRecords;
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

/**
 * Cambia la página actual.
 * 
 * @param {number} newPage - El número de la página que se desea mostrar.
 * @example
 * changePage(2);
 */
function changePage(newPage) {
    if (newPage < 1 || newPage > totalPages) {
        // Evita que la página sea menor que 1 o mayor que el número total de páginas
    fetchCompraDetalles(currentPage, pageSize, loteID, compraID, productoID);
}
}

/**
 * Cambia el tamaño de la página.
 * 
 * @param {number} newSize - El nuevo tamaño de la página.
 * @example
 * changePageSize(10);
 */
function changePageSize(newSize) {
    pageSize = newSize;
    fetchCompraDetalles(currentPage, pageSize, loteID, compraID, productoID);
}

// ************************************************************************* //
// ************* Llamada inicial para cargar la primera página ************* //
// ************************************************************************* //

fetchCompraDetalles(currentPage, pageSize, loteID, compraID, productoID);
