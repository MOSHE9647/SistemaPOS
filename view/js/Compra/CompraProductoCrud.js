// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de CompraProducto ************* //
// ************************************************************************************************ //

/**
 * Crea una nueva compra de producto.
 */
function createCompraProducto() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Verificar y obtener el ID del proveedor desde el campo select
        if (fieldName === 'proveedornombre') {
            value = document.getElementById('proveedorid-select').value;
            fieldName = 'compraproductoproveedorid'; // Cambia el nombre de la clave para coincidir con lo que PHP espera
        }

        data[fieldName] = value;
    });

    // Mostrar datos recolectados para depuración
    console.log('Datos a enviar:', data);

    fetch('../controller/CompraProductoAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json(); // Podría fallar si no se recibe un JSON válido
    })
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCompraProductos(currentPage, pageSize); // Recargar datos para reflejar la nueva compra
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al crear la compra. ${error}`, 'error');
        console.error('Error:', error); // Log para depuración
    });
}

/**
 * Actualiza una compra de producto existente.
 * 
 * @param {number} id - El ID de la compra de producto que se desea actualizar.
 */
function updateCompraProducto(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Verificar y obtener el ID del proveedor desde el campo select
        if (fieldName === 'proveedornombre') {
            value = row.querySelector('select').value;
            fieldName = 'compraproductoproveedorid'; // Cambia el nombre de la clave para coincidir con lo que PHP espera
        }

        data[fieldName] = value;
    });

    // Mostrar datos recolectados para depuración
    console.log('Datos a enviar:', data);

    fetch('../controller/CompraProductoAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCompraProductos(currentPage, pageSize); // Recargar datos para reflejar la actualización
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al actualizar la compra. ${error}`, 'error');
        console.error('Error:', error); // Log para depuración
    });
}

/**
 * Elimina una compra de producto existente.
 * 
 * @param {number} id - El ID de la compra de producto que se desea eliminar.
 */
function deleteCompraProducto(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta compra de producto?')) {
        fetch('../controller/CompraProductoAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', compraproductoid: id }), // Cambiar clave a compraproductoid
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                fetchCompraProductos(currentPage, pageSize); // Recargar datos para reflejar la eliminación
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage(`Ocurrió un error al eliminar la compra. ${error}`, 'error');
            console.error('Error:', error); // Log para depuración
        });
    }
}
