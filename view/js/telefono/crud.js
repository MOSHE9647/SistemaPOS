// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo telefono enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en el elemento #createRow.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito, recarga los datos de telefonos y elimina la fila de creación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * createTelefono();
 * 
 * @returns {void}
 */
function createTelefono() {
    // Obtener la fila de creación de telefono
    let row = document.getElementById('createRow');
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input, select');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'insertar' };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o valor)
        let fieldName = input.closest('td').dataset.field;

        // Agregar el campo al objeto de datos
        data[fieldName] = input.value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/telefonoAction.php', {
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
            // Mostrar mensaje de éxito
            showMessage(data.message, 'success');
            
            // Recargar los datos de telefonos para reflejar la creación del nuevo telefono
            fetchTelefonos(currentPage, pageSize, sort);
            
            // Eliminar la fila de creación de telefono
            document.getElementById('createRow').remove();
            
            // Mostrar el botón de creación de telefono nuevamente
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al crear el nuevo telefono.<br>${error}`, 'error');
    });
}

/**
 * Actualiza un telefono existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en la fila con el id especificado.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de telefonos para reflejar la actualización.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del telefono a actualizar
 * 
 * @example
 * updateTelefono(1); // Actualizar el telefono con id 1
 * 
 * @returns {void}
 */
function updateTelefono(id) {
    // Obtener la fila del telefono con el id especificado
    let row = document.querySelector(`tr[data-id='${id}']`);
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input, select');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'actualizar', id: id };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o valor)
        let fieldName = input.closest('td').dataset.field;

        // Agregar el campo al objeto de datos
        data[fieldName] = input.value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/telefonoAction.php', {
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
            // Mostrar mensaje de éxito
            showMessage(data.message, 'success');
            
            // Recargar los datos de telefonos para reflejar la actualización
            fetchTelefonos(currentPage, pageSize, sort);
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar el telefono.<br>${error}`, 'error');
    });
}

/**
 * Elimina un telefono existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de eliminar el telefono.
 *              Si el usuario confirma, envía una solicitud POST al servidor con el id del telefono a eliminar.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de telefonos para reflejar la eliminación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del telefono a eliminar
 * 
 * @example
 * deleteTelefono(1); // Eliminar el telefono con id 1
 * 
 * @returns {void}
 */
function deleteTelefono(id) {
    // Solicitar confirmación al usuario antes de eliminar el telefono
    if (confirm('¿Estás seguro de que deseas eliminar este telefono?')) {
        // Enviar la solicitud POST al servidor con el id del telefono a eliminar
        fetch('../controller/telefonoAction.php', {
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
                
                // Recargar los datos de telefonos para reflejar la eliminación
                fetchTelefonos(currentPage, pageSize, sort);
            } else {
                // Mostrar mensaje de error
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar el telefono.<br>${error}`, 'error');
        });
    }
}