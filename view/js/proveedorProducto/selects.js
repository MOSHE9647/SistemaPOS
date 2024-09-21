

async function cargarProveedores(){
    try{
        const response = await fetch('../controller/proveedorAction.php?accion=listarProveedores');
        return await response.json();
    }catch(error){
        showMessage(`Ocurrio un error al obtener la lista de proveedores.<br>${error}`,'error');
        return {};
    }
}

cargarProveedores().then(data=>{
    if(data.success){
        window.listaP = data;
    }else{
        showMessage(data.message, 'error');
        window.listaP = {};
    }
    cargarSelectProveedores();
});

function cargarSelectProveedores(){
    const proveedorSelects = document.getElementById('proveedor-select');
    let value = proveedorSelects.value;
    proveedorSelects.innerHTML = '<option value="">...Seleccionar...</option>';
    if(window.listaP.listaProveedores){
        window.listaP.listaProveedores.forEach(proveedor => {
            const opcion = document.createElement('option');
            opcion.value = proveedor.ID;
            opcion.textContent = proveedor.Nombre;
            opcion.selected = opcion.value === value;
            proveedorSelects.appendChild(opcion);
        });
    }
}