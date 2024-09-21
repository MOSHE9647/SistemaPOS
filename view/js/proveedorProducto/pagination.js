const DEFAULT_PAGE_SIZE = 5;

let sort = 'nombre'
let proveedor = 0;
let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
let pageSize = DEFAULT_PAGE_SIZE;

function fetchProductos(proveedorID, page, size, sort){

    fetch(`../controller/proveedorProductoAction.php?page=${page}&size=${size}&sort=${sort}&id=${proveedorID}`)
    .then(response => response.json())
    .then(data=>{
        if(data.success){
            renderTable(data.productos);
            proveedor = proveedorID;
            currentPage = data.page;
            totalPages = data.totalPages;
            totalRecords = data.totalRecords;
            pageSize = data.size;
            cargarControlPaginacion();
        }else{
            showMessage(data.message, 'error');
        }
    }).catch(error=>{
        showMessage(`Ocurrió un error al obtener la lista de productos.<br>${error}`, 'error');
    });
}

function cargarControlPaginacion(){
    document.getElementById('totalRecords').textContent = totalRecords;
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

function cambiarPagina(newPage){
    if (newPage >= 1 && newPage <= totalPages) {
        //cargar tabla
        fetchProductos(proveedor,page,pageSize,sort);
    }
}

function cambiarTamanoPagina(newSize){
    pageSize = newSize;
    //cargar tabla
    fetchProductos(proveedor,currentPage,pageSize,sort);
}

function cambiarOrdenamiento(newSort){
    sort = newSort;
    //cargar tabla
    fetchProductos(proveedor,currentPage,pageSize,sort);
}

document.getElementById('pageSizeSelector').addEventListener('change',(event)=>{
    cambiarTamanoPagina(event.target.value);
});

document.getElementById('sortSelector').addEventListener('change',(event)=>{
    cambiarOrdenamiento(event.target.value);
});