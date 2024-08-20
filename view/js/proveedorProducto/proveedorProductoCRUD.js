/**
 * Crea un registro Proveedor-Producto.
 */
function createProveedorProducto() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        data[fieldName] = value;
    });

    fetch('../controller/proveedorProductoAction.php', {
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
            fetchProveedorProductos(currentPage, pageSize);
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al crear la relación.<br>${error}`, 'error');
    });
}

/**
 * Actualiza un registro Proveedor-Producto existente.
 */
function updateProveedorProducto(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        data[fieldName] = value;
    });

    fetch('../controller/proveedorProductoAction.php', {
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
            fetchProveedorProductos(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al actualizar la relación.<br>${error}`, 'error');
    });
}

/**
 * Elimina un registro Proveedor-Producto existente.
 */
function deleteProveedorProducto(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
        fetch('../controller/proveedorProductoAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                fetchProveedorProductos(currentPage, pageSize);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage(`Ocurrió un error al eliminar el registro.<br>${error}`, 'error');
        });
    }
}
