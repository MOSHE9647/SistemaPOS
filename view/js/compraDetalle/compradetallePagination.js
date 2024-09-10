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

// ************************************************************************** //
// ************* Métodos para manipulación dinámica de la tabla ************* //
// ************************************************************************** //

/**
 * Obtiene una lista de detalles de compra del servidor y las renderiza en una tabla.
 * 
 * @param {number} page - El número de página a obtener (índice 1).
 * @param {number} size - El número de registros a obtener por página.
 * 
 * @example
 * fetchCompraDetalles(1, 10); // Obtiene los primeros 10 registros.
 * 
 * @returns {undefined}
 */
function fetchCompraDetalles(page, size) {
    fetch(`../controller/compradetalleAction.php?page=${page}&size=${size}`)
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
        return;
    }
    currentPage = newPage;
    fetchCompraDetalles(currentPage, pageSize);
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
    fetchCompraDetalles(currentPage, pageSize);
}

// ************************************************************************* //
// ************* Llamada inicial para cargar la primera página ************* //
// ************************************************************************* //

fetchCompraDetalles(currentPage, pageSize);
