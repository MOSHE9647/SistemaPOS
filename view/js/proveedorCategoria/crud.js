



async function createCategoria() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Convertir 'Distancia' a double
        if (fieldName === 'distancia') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }
        data[fieldName] = value;
    });
    console.log('>>>>    ' +data);
    try {
        let response = await fetch('../controller/categoriaAction.php', {
            method: 'POST',
            body: new URLSearchParams(data),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al crear la nueva categoria.<br>${error.message}` };
    }
}


function updateCategoria(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Convertir 'Distancia' a double
        if (fieldName === 'distancia') {
            value = parseFloat(value).toFixed(2); 
        }

        data[fieldName] = value;
    });

    fetch('../controller/categoriaAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        } 
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');

            const proveedor = document.getElementById('proveedor-select').value;
            const proveedorID = parseInt(proveedor);

            fetchCategoria(proveedorID, currentPage, pageSize, sort); 
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al actualizar la dirección.<br>${error}`, 'error');
    });
}

async function deleteCategoria(id) {
    try {
        let response = await fetch('../controller/categoriaAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al eliminar la dirección.<br>${error.message}` };
    }
}

async function addCategoriaToProveedor() {
    const response = await createCategoria();
    console.log('-----> '+response);
    if (response.success) {
        console.log('Dirección creada con ID:', response.id);
    } else if (response.message) {
        console.error('Error al crear la categoria:', response.message);
        showMessage(response.message, 'error');
        return;
    }

    // Obtener el id del proveedor
    const proveedor = document.getElementById('proveedor-select').value;
    const proveedorID = parseInt(proveedor);

    let dataToSend = {
        accion: 'insertar',
        proveedorid: proveedorID,
        categoriaid: response.id
    };

    // Enviar la solicitud POST al servidor
    fetch('../controller/proveedorCategoriaAction.php', {
        method: 'POST',
        body: new URLSearchParams(dataToSend),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCategoria(proveedorID, currentPage, pageSize, sort);
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
            deleteCategoria(response.id).then(() => {
                if (data.success) {
                    console.log('categoria eliminada después del fallo');
                } else if (data.message) {
                    console.error('Error al eliminar la categoria:', data.message);
                    showMessage(data.message, 'error');
                    return;
                }
            });
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al agregar la categoria al proveedor.<br>${error}`, 'error');
        deleteCategoria(response.id).then(() => {
            if (data.success) {
                console.log('Categoria eliminada después del fallo');
            } else if (data.message) {
                console.error('Error al eliminar la categoria:', data.message);
                showMessage(data.message, 'error');
                return;
            }
        });
    });
}

function removeCategoriaFromProveedor(categoriaID) {
    // Solicitar confirmación al usuario antes de eliminar la dirección
    if (!confirm('¿Está seguro de que desea eliminar esta dirección?')) {
        return;
    }

    // Obtener el id del proveedor
    const proveedor = document.getElementById('proveedor-select').value;
    const proveedorID = parseInt(proveedor);

    // Crear un objeto para almacenar los datos a enviar al servidor
    let dataToSend = {
        accion: 'eliminar',
        proveedorid: proveedorID,
        categoriaid: categoriaID
    };

    // Enviar la solicitud POST al servidor con el id de la dirección a eliminar
    fetch('../controller/proveedorCategoriaAction.php', {
        method: 'POST',
        body: new URLSearchParams(dataToSend),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCategoria(proveedorID, currentPage, pageSize, sort);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al eliminar la categoria del proveedor.<br>${error}`, 'error');
    });
}