// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea una nueva cuenta por pagar.
 * 
 * @description Envía una solicitud POST al servidor para crear una nueva cuenta por pagar con los datos ingresados en la fila de creación.
 * @example
 * createCuentaPorPagar();
 */
function createCuentaPorPagar() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;
  
        data[fieldName] = value;
    });
    console.log('Datos enviados para crear cuenta por pagar:', data); // Mensaje de depuración

    fetch('../controller/CuentaPorPagarAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor al crear cuenta por pagar:', data); // Mensaje de depuración
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCuentaPorPagar(currentPage, pageSize);
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al crear la nueva cuenta por pagar.<br>${error}`, 'error');
    });
}

/**
 * Actualiza una cuenta por pagar existente.
 * 
 * @param {number} id - El ID de la cuenta por pagar que se desea actualizar.
 * @description Envía una solicitud POST al servidor para actualizar la cuenta por pagar con los datos ingresados en la fila de edición.
 * @example
 * updateCuentaPorPagar(123);
 */
function updateCuentaPorPagar(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        data[fieldName] = value;
    });

    console.log('Datos enviados:', data); // Para depuración

    fetch('../controller/CuentaPorPagarAction.php', {
        method: 'POST',
        body: new URLSearchParams(data).toString(),  // Convierte a string
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCuentaPorPagar(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al actualizar la cuenta por pagar.<br>${error}`, 'error');
    });
}

/**
 * Elimina una cuenta por pagar existente.
 * 
 * @param {number} id - El ID de la cuenta por pagar que se desea eliminar.
 * @description Envía una solicitud POST al servidor para eliminar la cuenta por pagar después de confirmar con el usuario.
 * @example
 * deleteCuentaPorPagar(123);
 */
function deleteCuentaPorPagar(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta cuenta por pagar?')) {
        fetch('../controller/CuentaPorPagarAction.php', {
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
                fetchCuentaPorPagar(currentPage, pageSize); // Recargar datos para reflejar la eliminación
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Muestra el mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar la cuenta por pagar.<br>${error}`, 'error');
        });
    }
}
