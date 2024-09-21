
async function createProducto(){
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = {accion: 'insertar'};

    inputs.forEach(input=>{
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        data[fieldName] = value;
    });
    fetch('../controller/productoAction.php',{
        method:'POST',
        body:new URLSearchParams(data),
        headers:{
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    }).then(response => response.json())
    .then(data=>{
        if(data.success){
            showMessage(data.message,'success');
            //cargarlo a proveedor producto
            insertProductoToProveedor(data.id);
            fetchProductos(proveedor,currentPage, pageSize, sort);
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';

        }else{
            showMessage(data.message,'error');
        }
    }).catch(error => {
        showMessage(`Ocurrio un error al crear un nuevo producto.<br>${error}`,'error');
    });
}

async function updateProducto(id){
    let row = document.querySelector(`tr[data-id='${id}']`)
    let inputs = row.querySelectorAll('input, select');
    let data ={accion: 'actualizar',id: id};

    inputs.forEach(input=>{
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        data[fieldName] = value;
    })

    fetch('../controller/productoAction.php',{
        method:'POST',
        body:new URLSearchParams(data),
        headers:{
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    }).then(response => response.json())
        .then(data =>{
        if(data.success){
            showMessage(data.message,'success');
            fetchProductos(proveedor,currentPage, pageSize, sort);
        }else{
            showMessage(data.message,'error');
        }
        })
        .catch(error =>{
            showMessage(`Ocurrio un error al intentar actualizar el producto.<br>${error}`,'error');
        });
}

async function deleteProducto(id){
    if(confirm('¿Estás seguro que quieres eliminar este producto para este proveedor?')){
        fetch('../controller/proveedorProductoAction.php',{
            method: 'POST',
            body: new URLSearchParams({accion: 'eliminar',proveedorid: proveedor, productoid: id}),
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data=>{
            if(data.success){
                showMessage(data.message,'success');
                
                fetchProductos(proveedor,currentPage, pageSize, sort);
            }else{
                showMessage(data.message,'error');
            }
        })
        .catch(error =>{
            showMessage(`Ocurrio un error al eliminar este producto para este proveedor.<br>${error}`,'error');
        })
    }
} 

function insertProductoToProveedor(id){
    fetch('../controler/proveedorProductoAction.php',{
        method: 'POST',
        body: new URLSearchParams({accion: 'insertar', proveedorid:proveedor, productoid: id}),
        headers:{
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    }).then(response=>response.json())
    .then(data=>{
        if(data.success){
            showMessage(data.message,'success');
        }else{
            showMessage(data.message, 'error');
        }
    }).catch(error=>{
        showMessage(`Ocurrio un error al insertar el producto al proveedor.<br>${error}`,'error');
    });
}