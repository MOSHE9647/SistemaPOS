// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea una nueva presentación enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en el elemento #createRow.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito, recarga los datos de presentaciones y elimina la fila de creación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * createPresentacion();
 * 
 * @returns {void}
 */
function createPresentacion() {
    let row = document.getElementById('createRow'); // Obtener la fila de creación de presentación
    let inputs = row.querySelectorAll('input'); // Obtener los campos de entrada de la fila
    let data = { accion: 'insertar' }; // Crear un objeto para almacenar los datos a enviar al servidor

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o descripción)
        let fieldName = input.closest('td').dataset.field;

        // Agregar el campo al objeto de datos
        data[fieldName] = input.value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/presentacionAction.php', {
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
            if (!data.inactive) {
                showMessage(data.message, 'success'); // Mostrar mensaje de éxito
                fetchPresentaciones(currentPage, pageSize, sort); // Recargar los datos de presentaciones para reflejar la creación
                document.getElementById('createRow').remove(); // Eliminar la fila de creación
                document.getElementById('createButton').style.display = 'inline-block'; // Mostrar el botón de crear
                return;
            }

            // Actualizar la presentación con los nuevos datos
            if (data.inactive && confirm(data.message)) { updatePresentacion(data.id, true); }
            else { showMessage('No se agregó la presentación', 'info'); }
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al crear la nueva presentación.<br>${error}`, 'error');
    });
}

/**
 * Actualiza una presentación existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en la fila con el id especificado.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de presentaciones para reflejar la actualización.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id de la presentación a actualizar
 * 
 * @example
 * updatePresentacion(1); // Actualizar la presentación con id 1
 * 
 * @returns {void}
 */
function updatePresentacion(id, reactivate = false) {
    let row;
    if (!reactivate) {
        row = document.querySelector(`tr[data-id='${id}']`); // Obtener la fila de la tabla con el id especificado
    } else {
        row = document.getElementById('createRow'); // Obtener la fila de creación de presentación
    }

    // Si no se encuentra la fila, salir de la función
    if (!row) {
        showMessage('No se encontró la fila de la presentación a actualizar', 'error'); 
        return; 
    }
    let inputs = row.querySelectorAll('input'); // Obtener los campos de entrada de la fila
    let data = { accion: 'actualizar', id: id }; // Crear un objeto para almacenar los datos a enviar al servidor

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o descripción)
        let fieldName = input.closest('td').dataset.field;

        // Agregar el campo al objeto de datos
        data[fieldName] = input.value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/presentacionAction.php', {
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
            showMessage(data.message, 'success'); // Mostrar mensaje de éxito
            fetchPresentaciones(currentPage, pageSize, sort); // Recargar los datos de presentaciones para reflejar la actualización
        } else {
            showMessage(data.message, 'error'); // Mostrar mensaje de error
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar la presentación.<br>${error}`, 'error');
    });
}

/**
 * Elimina una presentación existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de eliminar la presentación.
 *              Si el usuario confirma, envía una solicitud POST al servidor con el id de la presentación a eliminar.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de presentaciones para reflejar la eliminación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id de la presentación a eliminar
 * 
 * @example
 * deletePresentacion(1); // Eliminar la presentación con id 1
 * 
 * @returns {void}
 */
function deletePresentacion(id) {
    // Solicitar confirmación al usuario antes de eliminar la presentación
    if (confirm('¿Estás seguro de que deseas eliminar esta presentación?')) {
        // Enviar la solicitud POST al servidor con el id de la presentación a eliminar
        fetch('../controller/presentacionAction.php', {
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
                // Mostrar mensaje de éxito
                showMessage(data.message, 'success');
                
                // Recargar los datos de presentaciones para reflejar la eliminación
                fetchPresentaciones(currentPage, pageSize, sort);
            } else {
                // Mostrar mensaje de error
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar la presentación.<br>${error}`, 'error');
        });
    }
}
