

const DEFAULT_PAGE_SIZE = 5; 

let sort = 'nombre';
let proveedor = 0;
let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
let pageSize = DEFAULT_PAGE_SIZE;

function fetchCategoria(proveedorID, page, size, sort) {
    console.log(page + '  '+ size +' '+ sort);



    fetch(`../controller/proveedorCategoriaAction.php?page=${page}&size=${size}&sort=${sort}&proveedor=${proveedorID}`)
        .then(response => response.json())
        .then(data => {
        console.log(data);
            if (data.success) {
                renderTable(data.categorias);
                proveedor = proveedorID;
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
            showMessage(`Ocurrió un error al obtener la lista de categorias.<br>${error}`, 'error');
        });
}


function updatePaginationControls() {
    document.getElementById('totalRecords').textContent = totalRecords;
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}


function changePage(newPage) {
    if (newPage >= 1 && newPage <= totalPages) {
        fetchCategoria(proveedor, newPage, pageSize, sort);
    }
}

function changePageSize(newSize) {
    pageSize = newSize;
    fetchCategoria(proveedor, currentPage, pageSize, sort);
}


function changePageSort(newSort) {
    sort = newSort;
    fetchCategoria(proveedor, currentPage, pageSize, sort);
}

document.getElementById('pageSizeSelector').addEventListener('change', (event) => {
    changePageSize(event.target.value);
});

document.getElementById('sortSelector').addEventListener('change', (event) => {
    changePageSort(event.target.value);
});