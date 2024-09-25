// *********************************************************************** //
// ************* Métodos para manejar la paginación y filtrado ************ //
// *********************************************************************** //

let currentPage = 1;
let pageSize = 5;
let totalPages = 1;
let sort = 'nombre'; // Ordenamiento por defecto

/**
 * Obtiene y renderiza las marcas con paginación y ordenamiento.
 * 
 * @description Esta función obtiene los datos de marcas del servidor y los renderiza en la tabla.
 *              También actualiza la paginación basada en los resultados obtenidos.
 * 
 * @example
 * fetchMarcas(1, 5, 'nombre');
 * 
 * @param {number} page - El número de página actual
 * @param {number} size - El tamaño de página (cantidad de registros por página)
 * @param {string} sortBy - El campo por el que se desea ordenar
 * 
 * @returns {void}
 */
function fetchMarcas(page = 1, size = 5, sortBy = 'nombre') {
    currentPage = page;
    pageSize = size;
    sort = sortBy;

    fetch(`../controller/marcaAction.php?accion=listarMarcas&page=${page}&size=${size}&sort=${sortBy}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaMarcas); // Renderizar la tabla de marcas
                totalPages = data.totalPages;
                updatePaginationControls(); // Actualizar los controles de paginación
                document.getElementById('totalRecords').innerText = data.totalRecords;
                document.getElementById('currentPage').innerText = currentPage;
                document.getElementById('totalPages').innerText = totalPages;
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage(`Error al obtener los datos de las marcas: ${error}`, 'error');
        });
}

/**
 * Actualiza los controles de paginación.
 * 
 * @description Habilita o deshabilita los botones de paginación según la página actual y el total de páginas.
 * 
 * @example
 * updatePaginationControls();
 * 
 * @returns {void}
 */
function updatePaginationControls() {
    document.getElementById('prevPage').disabled = currentPage <= 1;
    document.getElementById('nextPage').disabled = currentPage >= totalPages;
}

/**
 * Cambia la página actual y recarga los datos de marcas.
 * 
 * @description Esta función cambia la página actual y obtiene los datos correspondientes a esa página.
 * 
 * @example
 * changePage(2);
 * 
 * @param {number} page - El número de página al que se desea cambiar
 * 
 * @returns {void}
 */
function changePage(page) {
    if (page >= 1 && page <= totalPages) {
        fetchMarcas(page, pageSize, sort);
    }
}

/**
 * Cambia el tamaño de página y recarga los datos de marcas.
 * 
 * @description Esta función cambia el tamaño de página y obtiene los datos correspondientes.
 * 
 * @example
 * changePageSize(10);
 * 
 * @param {number} size - El nuevo tamaño de página
 * 
 * @returns {void}
 */
function changePageSize(size) {
    pageSize = size;
    fetchMarcas(1, pageSize, sort); // Recargar desde la primera página
}

// Inicializar paginación y sort cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    fetchMarcas(currentPage, pageSize, sort); // Cargar las marcas

    // Configurar el evento para cambiar el ordenamiento
    document.getElementById('sortSelector').addEventListener('change', function() {
        sort = this.value;
        fetchMarcas(1, pageSize, sort);
    });

    // Configurar el evento para cambiar el tamaño de página
    document.getElementById('pageSizeSelector').addEventListener('change', function() {
        changePageSize(this.value);
    });
});
