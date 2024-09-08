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
 * Obtiene una lista de compras del servidor y las renderiza en una tabla.
 * 
* @param {number} página - El número de página a obtener (índice 1).
 * @param {number} tamaño - El número de registros a obtener por página.
 * 
 * @example
 * fetchCompras(1, 10); // Obtiene los primeros 10 registros.
 * 
 * @returns {undefined}
 */
function fetchCompras(page, size) {
    fetch(`../controller/compraAction.php?page=${page}&size=${size}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Compras recibidas:', data);
                renderTable(data.listaCompras);
                currentPage = data.page;
                totalPages = data.totalPages;
                totalRecords = data.totalRecords;
                pageSize = data.size;
                updatePaginationControls();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage(`Ocurrió un error al obtener la lista de compras.<br>${error}`, 'error');
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
        return;
    }
    currentPage = newPage;
    fetchCompras(currentPage, pageSize);
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
    fetchCompras(currentPage, pageSize);
}

// ************************************************************************* //
// ************* Llamada inicial para cargar la primera página ************* //
// ************************************************************************* //

fetchCompras(currentPage, pageSize);
