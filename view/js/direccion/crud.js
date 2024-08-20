// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea una nueva dirección.
 * 
 * @description Envía una solicitud POST al servidor para crear una nueva dirección con los datos ingresados en la fila de creación.
 * @example
 * createDireccion();
 */
function createDireccion() {
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

    fetch('../controller/direccionAction.php', {
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
            fetchDirecciones(currentPage, pageSize, sort); // Recargar datos para reflejar la creación de la direccion
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Muestra el mensaje de error detallado
        showMessage(`Ocurrió un error al crear la nueva dirección.<br>${error}`, 'error');
    });
}

/**
 * Actualiza una dirección existente.
 * 
 * @param {number} id - El ID de la dirección que se desea actualizar.
 * @description Envía una solicitud POST al servidor para actualizar la dirección con los datos ingresados en la fila de edición.
 * @example
 * updateDireccion(123);
 */
function updateDireccion(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Convertir 'Distancia' a double
        if (fieldName === 'distancia') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

        data[fieldName] = value;
    });

    fetch('../controller/direccionAction.php', {
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
            fetchDirecciones(currentPage, pageSize, sort); // Recargar datos para reflejar la actualización de la direccion
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Muestra el mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar la dirección.<br>${error}`, 'error');
    });
}

/**
 * Elimina una dirección existente.
 * 
 * @param {number} id - El ID de la dirección que se desea eliminar.
 * @description Envía una solicitud POST al servidor para eliminar la dirección después de confirmar con el usuario.
 * @example
 * deleteDireccion(123);
 */
function deleteDireccion(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta dirección?')) {
        fetch('../controller/direccionAction.php', {
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
                fetchDirecciones(currentPage, pageSize, sort); // Recargar datos para reflejar la eliminación de la direccion
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Muestra el mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar la dirección.<br>${error}`, 'error');
        });
    }
}