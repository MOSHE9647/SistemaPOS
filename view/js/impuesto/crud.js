// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo impuesto enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en el elemento #createRow,
 *              convierte el campo 'valor' a un número decimal con 2 lugares decimales y los envía al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito, recarga los datos de impuestos y elimina la fila de creación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * // Asumiendo que el elemento #createRow tiene la siguiente estructura:
 * // <tr id="createRow">
 * //   <td data-field="nombre"><input type="text" value="Nuevo Impuesto"></td>
 * //   <td data-field="valor"><input type="number" value="10.50"></td>
 * // </tr>
 * createImpuesto();
 * 
 * @returns {void}
 */
function createImpuesto() {
    let row = document.getElementById('createRow'); //<- Obtener la fila de creación de impuesto
    let inputs = row.querySelectorAll('input'); //<- Obtener los campos de entrada de la fila
    let data = { accion: 'insertar' }; //<- Objeto para almacenar los datos a enviar al servidor

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field; //<- Obtener el nombre del campo (nombre, fecha o valor)
        let value = input.value; //<- Obtener el valor del campo

        // Convertir el campo 'valor' a un número decimal con 2 lugares decimales
        if (fieldName === 'valor') {
            value = parseFloat(value).toFixed(2); // Convertir a número decimal y limitar a 2 decimales
        }

        // Agregar el campo al objeto de datos
        data[fieldName] = value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/impuestoAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Si la solicitud es exitosa
        if (data.success) {
            // Si el impuesto no está inactivo
            if (!data.inactive) {
                showMessage(data.message, 'success'); //<- Mostrar mensaje de éxito
                fetchImpuestos(currentPage, pageSize, sort); //<- Recargar los datos de impuestos para reflejar la creación
                document.getElementById('createRow').remove(); //<- Eliminar la fila de creación
                document.getElementById('createButton').style.display = 'inline-block'; //<- Mostrar el botón de crear
                return;
            }

            // Actualizar el impuesto con los nuevos datos
            if (data.inactive && confirm(data.message)) { updateImpuesto(data.id, true); }
            else { showMessage('No se agregó el impuesto', 'info'); }
        } else {
            showMessage(data.message, 'error'); //<- Mostrar mensaje de error
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al crear el nuevo impuesto.<br>${error}`, 'error');
    });
}

/**
 * Actualiza un impuesto existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en la fila con el id especificado,
 *              convierte el campo 'valor' a un número decimal con 2 lugares decimales y los envía al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de impuestos para reflejar la actualización.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del impuesto a actualizar
 * 
 * @example
 * updateImpuesto(1); // Actualizar el impuesto con id 1
 * 
 * @returns {void}
 */
function updateImpuesto(id, reactivate = false) {
    let row;
    if (!reactivate) {
        row = document.querySelector(`tr[data-id='${id}']`); //<- Obtener la fila de la tabla con el id especificado
    } else {
        row = document.getElementById('createRow'); //<- Obtener la fila de creación de impuesto
    }

    // Si no se encuentra la fila, salir de la función
    if (!row) {
        showMessage('No se encontró la fila del impuesto del impuesto a actualizar', 'error'); 
        return; 
    }
    let inputs = row.querySelectorAll('input'); //<- Obtener los campos de entrada de la fila
    let data = { accion: 'actualizar', id: id }; //<- Objeto para almacenar los datos a enviar al servidor

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field; //<- Obtener el nombre del campo (nombre, fecha o valor)
        let value = input.value; //<- Obtener el valor del campo

        // Convertir el campo 'valor' a un número decimal con 2 lugares decimales
        if (fieldName === 'valor') {
            value = parseFloat(value).toFixed(2); // Convertir a número decimal y limitar a 2 decimales
        }

        // Agregar el campo al objeto de datos
        data[fieldName] = value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/impuestoAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Si la solicitud es exitosa
        if (data.success) {
            showMessage(data.message, 'success'); //<- Mostrar mensaje de éxito
            fetchImpuestos(currentPage, pageSize, sort); //<- Recargar los datos de impuestos para reflejar la actualización
        } else {
            showMessage(data.message, 'error'); //<- Mostrar mensaje de error
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar el impuesto.<br>${error}`, 'error');
    });
}

/**
 * Elimina un impuesto existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de eliminar el impuesto.
 *              Si el usuario confirma, envía una solicitud POST al servidor con el id del impuesto a eliminar.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de impuestos para reflejar la eliminación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del impuesto a eliminar
 * 
 * @example
 * deleteImpuesto(1); // Eliminar el impuesto con id 1
 * 
 * @returns {void}
 */
function deleteImpuesto(id) {
    // Solicitar confirmación al usuario antes de eliminar el impuesto
    if (confirm('¿Estás seguro de que deseas eliminar este impuesto?')) {
        // Enviar la solicitud POST al servidor con el id del impuesto a eliminar
        fetch('../controller/impuestoAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Si la solicitud es exitosa
            if (data.success) {
                showMessage(data.message, 'success'); //<- Mostrar mensaje de éxito
                fetchImpuestos(currentPage, pageSize, sort); //<- Recargar los datos de impuestos para reflejar la eliminación
            } else {
                // Mostrar mensaje de error
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar el impuesto.<br>${error}`, 'error');
        });
    }
}