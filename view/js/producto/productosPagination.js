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
let codigoBarrasID = 0;
let categoriaID = 0;
let subCategoriaID = 0;
let marcaID = 0;
let presentacionID = 0;

// ************************************************************************** //
// ************* Métodos para manipulación dinámica de la tabla ************* //
// ************************************************************************** //

/**
 * Obtiene una lista de lotes del servidor y las renderiza en una tabla.
 * 
 * @param {number} page - El número de página a obtener (índice 1).
 * @param {number} size - El número de registros a obtener por página.
 * @param {number} codigoBarrasID - El número de página a obtener (índice 1).
 * @param {number} categoriaID - El número de registros a obtener por página.
 * @param {number} subCategoriaID - El número de página a obtener (índice 1).
 * @param {number} marcaID - El número de registros a obtener por página.
 * @param {number} presentacionID - El número de registros a obtener por página.
 * 
 * @example
 * fetchLotes(1, 10, "lotecodigo"); // Obtiene los primeros 10 registros, ordenados por lote código.
 * 
 * @returns {undefined}
 */

/*function fetchProductos(page, size) {
    const url = `../controller/productoAction.php?page=${page}&size=${size}`;
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('La respuesta de la red no fue correcta: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log("Datos recibidos:", data); // Verifica que data contiene la información esperada
            if (data.success) {
                renderTable(data.listaProductos);
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
            console.error("Error de Fetch:", error);
            showMessage(`Ocurrió un error al obtener la lista de productos. Error: ${error}`, 'error');
        });
}*/


function fetchProductos(page, size, codigoBarrasID, categoriaID, subCategoriaID, marcaID, presentacionID) {
    const url = `../controller/productoAction.php?page=${page}&size=${size}&codigo=${codigoBarrasID}&categoria=${categoriaID}&sub=${subCategoriaID}&marca=${marcaID}&presentacion=${presentacionID}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Data:", data); // Verifica que data contiene la información esperada
                renderTable(data.listaProductos);

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
            showMessage(`Ocurrió un error al obtener la lista de productos. Error: ${error}`, 'error');
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
      fetchProductos(currentPage, pageSize, codigoBarrasID, categoriaID, subCategoriaID, marcaID, presentacionID);
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
    fetchProductos(currentPage, pageSize, codigoBarrasID, categoriaID, subCategoriaID, marcaID, presentacionID);
}




// ************************************************************************* //
// ************* Llamada inicial para cargar la primera página ************* //
// ************************************************************************* //

fetchProductos(currentPage, pageSize, codigoBarrasID, categoriaID, subCategoriaID, marcaID, presentacionID);