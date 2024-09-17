// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea una nueva compra.
 * 
 * @description Envía una solicitud POST al servidor para crear una nueva compra con los datos ingresados en la fila de creación.
 * @example
 * createCompra();
 */
function createCompra() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        if (fieldName === 'proveedornombre') {
            value = document.getElementById('proveedorid-select').value;
        } 
        data[fieldName] = value;
    });

    console.log('Datos enviados para crear compra:', data); // Mensaje de depuración

    fetch('../controller/compraAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor al crear compra:', data); // Mensaje de depuración
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCompras(currentPage, pageSize);
            document.getElementById('createRow').remove(); // Elimina la fila de creación
            document.getElementById('createButton').style.display = 'inline-block'; // Muestra el botón de crear
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al crear la nueva compra.<br>${error}`, 'error');
    });
}

/**
 * Actualiza una compra existente.
 * 
 * @param {number} id - El ID de la compra que se desea actualizar.
 * @description Envía una solicitud POST al servidor para actualizar la compra con los datos ingresados en la fila de edición.
 * @example
 * updateCompra(123);
 */
function updateCompra(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Asegúrate de obtener el valor correcto del proveedor
        if (fieldName === 'proveedornombre') {
            value = document.getElementById(`proveedorid-select-${id}`).value; // Obtener el valor correcto del select de proveedores
        }

        data[fieldName] = value;
    });

    console.log('Datos enviados para actualizar compra:', data); // Depuración

    fetch('../controller/compraAction.php', {
        method: 'POST',
        body: new URLSearchParams(data), // Convierte los datos al formato URL para enviarlos correctamente
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data); // Depuración
        if (data.success) {
            showMessage(data.message, 'success'); // Muestra un mensaje de éxito
            fetchCompras(currentPage, pageSize); // Recarga la tabla de compras para reflejar los cambios
        } else {
            showMessage(data.message, 'error'); // Muestra el mensaje de error del servidor
        }
    })
    .catch(error => {
        console.error('Error al actualizar la compra:', error); // Mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar la compra.<br>${error}`, 'error'); // Muestra un mensaje de error en la interfaz
    });
}


/**
 * Elimina una compra existente.
 * 
 * @param {number} id - El ID de la compra que se desea eliminar.
 * @description Envía una solicitud POST al servidor para eliminar la compra después de confirmar con el usuario.
 * @example
 * deleteCompra(123);
 */
function deleteCompra(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta compra?')) {
        fetch('../controller/compraAction.php', {
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
                fetchCompras(currentPage, pageSize); // Recargar datos para reflejar la eliminación de la compra
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Muestra el mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar la compra.<br>${error}`, 'error');
        });
    }
}
