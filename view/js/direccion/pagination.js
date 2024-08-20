// ********************************************************************************** //
// ************* Constantes y variables predefinidas para la paginación ************* //
// ********************************************************************************** //

// Constantes
const DEFAULT_PAGE_SIZE = 5; // Tamaño de página predeterminado

// Variables globales
let sort = 'provincia';
let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
let pageSize = DEFAULT_PAGE_SIZE;

// ************************************************************************** //
// ************* Métodos para manipulación dinámica de la tabla ************* //
// ************************************************************************** //

/**
 * Obtiene una lista de direcciones del servidor y las renderiza en una tabla.
 * 
 * @param {number} página - El número de página a obtener (índice 1).
 * @param {number} tamaño - El número de registros a obtener por página.
 * @param {string} orden - La columna por la que ordenar (por ejemplo, "id", "nombre", etc.).
 * 
 * @example
 * fetchDirecciones(1, 10, "nombre"); // Obtiene los primeros 10 registros, ordenados por nombre.
 * 
 * @returns {undefined}
 */
function fetchDirecciones(page, size, sort) {
    fetch(`../controller/direccionAction.php?page=${page}&size=${size}&sort=${sort}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaDirecciones);
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
            showMessage(`Ocurrió un error al obtener la lista de direcciones.<br>${error}`, 'error');
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
    if (newPage >= 1 && newPage <= totalPages) {
        fetchDirecciones(newPage, pageSize, sort);
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
    fetchDirecciones(currentPage, pageSize, sort);
}

/**
 * Cambia el orden de la lista.
 * 
 * @param {string} newSort - El nuevo orden de la lista (nombre de la columna en la bd).
 * @example
 * changePageSort('distancia');
 */
function changePageSort(newSort) {
    sort = newSort;
    fetchDirecciones(currentPage, pageSize, sort);
}

/**
 * Evento de cambio de tamaño de página.
 * 
 * @description Se ejecuta cuando el usuario selecciona un nuevo tamaño de página en el selector.
 * @param {Event} event - El evento de cambio.
 * 
 */
document.getElementById('pageSizeSelector').addEventListener('change', (event) => {
    changePageSize(event.target.value);
});

/**
 * Evento de cambio de orden de página.
 * 
 * @description Se ejecuta cuando el usuario selecciona un nuevo orden de página en el selector.
 * @param {Event} event - El evento de cambio.
 * 
 */
document.getElementById('sortSelector').addEventListener('change', (event) => {
    changePageSort(event.target.value);
});

// ************************************************************************* //
// ************* Llamada inicial para cargar la primera página ************* //
// ************************************************************************* //

fetchDirecciones(currentPage, pageSize, sort);