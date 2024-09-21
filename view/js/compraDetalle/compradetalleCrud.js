// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo detalle de compra.
 * 
 * @description Envía una solicitud POST al servidor para crear un nuevo detalle de compra con los datos ingresados en la fila de creación.
 * @example
 * createCompraDetalle();
 */
function createCompraDetalle() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        if (fieldName === 'lotecodigo') {
            value = document.getElementById('loteid-select').value;
        } 
        if (fieldName === 'compranumerofactura') {
            value = document.getElementById('compraid-select').value;
        } 
        if (fieldName === 'productonombre') {
            value = document.getElementById('productoid-select').value;
        } 
        data[fieldName] = value;
    });

    console.log('Datos enviados para crear detalle de compra:', data); // Mensaje de depuración

    fetch('../controller/compradetalleAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor al crear detalle de compra:', data); // Mensaje de depuración
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCompraDetalles(currentPage, pageSize);
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al crear el nuevo detalle de compra.<br>${error}`, 'error');
    });
}

/**
 * Actualiza un detalle de compra existente.
 * 
 * @param {number} id - El ID del detalle de compra que se desea actualizar.
 * @description Envía una solicitud POST al servidor para actualizar el detalle de compra con los datos ingresados en la fila de edición.
 * @example
 * updateCompraDetalle(123);
 */
function updateCompraDetalle(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;
        // Asegúrate de obtener el valor correcto del proveedor
         if (fieldName === 'lotecodigo') {
         value = document.getElementById(`loteid-select-${id}`).value; // Obtener el valor correcto del select de proveedores
         }
         if (fieldName === 'compranumerofactura') {
            value = document.getElementById(`compraid-select-${id}`).value; // Obtener el valor correcto del select de proveedores
        }
        if (fieldName === 'productonombre') {
            value = document.getElementById(`productoid-select-${id}`).value; // Obtener el valor correcto del select de proveedores
        }
        data[fieldName] = value;
    });

    console.log('Datos enviados para actualizar detalle de compra:', data); // Para depuración

    fetch('../controller/compradetalleAction.php', {
        method: 'POST',
        body: new URLSearchParams(data).toString(),  // Convierte a string
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data); // Depuración
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCompraDetalles(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error al actualizar el detalle compra:', error); // Mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar el detalle de compra.<br>${error}`, 'error');
    });
}

/**
 * Elimina un detalle de compra existente.
 * 
 * @param {number} id - El ID del detalle de compra que se desea eliminar.
 * @description Envía una solicitud POST al servidor para eliminar el detalle de compra después de confirmar con el usuario.
 * @example
 * deleteCompraDetalle(123);
 */
function deleteCompraDetalle(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este detalle de compra?')) {
        fetch('../controller/compradetalleAction.php', {
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
                fetchCompraDetalles(currentPage, pageSize); // Recargar datos para reflejar la eliminación
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Muestra el mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar el detalle de compra.<br>${error}`, 'error');
        });
    }
}
