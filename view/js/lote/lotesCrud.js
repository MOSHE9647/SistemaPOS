// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo lote.
 * 
 * @description Envía una solicitud POST al servidor para crear un nuevo lote con los datos ingresados en la fila de creación.
 * @example
 * createLote();
 */
function createLote() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field; // Obtener el nombre del campo desde el atributo 'data-field'
        let value = input.value; // Obtener el valor ingresado

        data[fieldName] = value; // Añadir los valores al objeto 'data'
    });

    console.log('Datos enviados para crear lote:', data); // Mensaje de depuración

    fetch('../controller/loteAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor al crear lote:', data); // Mensaje de depuración
        if (data.success) {
            showMessage(data.message, 'success');
            fetchLotes(currentPage, pageSize); // Recargar la lista de lotes
            // Limpiar los campos de entrada después de crear el lote
            inputs.forEach(input => input.value = '');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al crear el nuevo lote.<br>${error}`, 'error');
    });
}


/**
 * Actualiza un lote existente.
 * 
 * @param {number} id - El ID del lote que se desea actualizar.
 * @description Envía una solicitud POST al servidor para actualizar el lote con los datos ingresados en la fila de edición.
 * @example
 * updateLote(123);
 */
/*function updateLote(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Convertir 'Precio' a double
        if (fieldName === 'loteprecio') {
            value = parseFloat(value).toFixed(2);
        }

        // Obtener ID del proveedor y del producto
        if (fieldName === 'proveedornombre') {
            value = document.getElementById('proveedorid-select').value;
        } else if (fieldName === 'productonombre') {
            value = document.getElementById('productoid-select').value;
        }

        data[fieldName] = value;
    });
    console.log('Datos enviados:', data); // Para depuración

    fetch('../controller/loteAction.php', {
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
            fetchLotes(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al actualizar el lote.<br>${error}`, 'error');
    });
}
*/

/**
 * Actualiza un lote existente.
 * 
 * @param {number} id - El ID del lote que se desea actualizar.
 * @description Envía una solicitud POST al servidor para actualizar el lote con los datos ingresados en la fila de edición.
 * @example
 * updateLote(123);
 */
function updateLote(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field; // Obtener el nombre del campo
        let value = input.value; // Obtener el valor ingresado

        data[fieldName] = value; // Añadir los valores al objeto 'data'
    });

    console.log('Datos enviados para actualizar lote:', data); // Para depuración

    fetch('../controller/loteAction.php', {
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
            fetchLotes(currentPage, pageSize); // Recargar la lista de lotes actualizada
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al actualizar el lote.<br>${error}`, 'error');
    });
}

/**
 * Elimina un lote existente.
 * 
 * @param {number} id - El ID del lote que se desea eliminar.
 * @description Envía una solicitud POST al servidor para eliminar el lote después de confirmar con el usuario.
 * @example
 * deleteLote(123);
 */
function deleteLote(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este lote?')) {
        fetch('../controller/loteAction.php', {
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
                fetchLotes(currentPage, pageSize); // Recargar la lista de lotes actualizada
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage(`Ocurrió un error al eliminar el lote.<br>${error}`, 'error');
        });
    }
}


