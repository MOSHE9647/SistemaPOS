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
async function createTelefono() {
    // Obtener la fila de creación de telefono
    const row = document.getElementById('createRow');
    
    if (!row) {
        console.error('No se encontró la fila de creación de teléfono.');
        return { message: 'No se encontró la fila de creación de teléfono.' };
    }

    const inputs = row.querySelectorAll('input, select'); // Obtener los campos de entrada de la fila
    const data = { accion: 'insertar' }; // Crear un objeto para almacenar los datos a enviar al servidor

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        const fieldName = input.closest('td').dataset.field; // Obtener el nombre del campo (nombre o valor)
        data[fieldName] = input.value; // Agregar el campo al objeto de datos
    });

    try {
        // Enviar la solicitud POST al servidor
        const response = await fetch('../controller/telefonoAction.php', {
            method: 'POST',
            body: new URLSearchParams(data),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al crear el nuevo teléfono: ${error.message}` };
    }
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
function updateTelefono(id, reactivate = false) {
    let row;
    if (!reactivate) {
        row = document.querySelector(`tr[data-id='${id}']`); //<- Obtener la fila de la tabla con el id especificado
    } else {
        row = document.getElementById('createRow'); //<- Obtener la fila de creación de telefono
    }

    // Si no se encuentra la fila, salir de la función
    if (!row) {
        showMessage('No se encontró la fila del teléfono a actualizar', 'error'); 
        return; 
    }
    let inputs = row.querySelectorAll('input, select'); // Obtener los campos de entrada de la fila
    let data = { accion: 'actualizar', id: id }; // Crear un objeto para almacenar los datos a enviar al servidor

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
            showMessage(data.message, 'success'); // Mostrar mensaje de éxito
            fetchTelefonos(usuario, currentPage, pageSize, sort); // Recargar los datos de telefonos para reflejar la actualización
        } else {
            showMessage(data.message, 'error'); // Mostrar mensaje de error
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
async function deleteTelefono(id) {
    try {
        // Enviar la solicitud POST al servidor con el id del telefono a eliminar
        const response = await fetch('../controller/telefonoAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al eliminar el telefono.<br>${error}` };
    }
}

async function addTelefonoToUsuario() {
    const response = await createTelefono();

    if (!response.success) {
        showMessage(response.message, 'error');
        return;
    }

    if (response.inactive) {
        if (confirm(response.message)) { updateTelefono(response.id, true); }
        else { 
            showMessage('No se le agregó el teléfono al usuario.', 'info'); 
            return;
        }
    }

    // Obtener el id del usuario
    const usuario = document.getElementById('usuario-select').value;
    const usuarioID = parseInt(usuario);

    // Crear un objeto para almacenar los datos a enviar al servidor
    let dataToSend = {
        accion: 'agregar',
        usuario: usuarioID,
        telefono: response.id
    };

    // Enviar la solicitud POST al servidor
    fetch('../controller/usuarioTelefonoAction.php', {
        method: 'POST',
        body: new URLSearchParams(dataToSend),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Si la solicitud es exitosa
        if (data.success) {
            showMessage(data.message, 'success'); // Mostrar mensaje de éxito
            fetchTelefonos(usuarioID, currentPage, pageSize, sort); // Recargar los datos de telefonos para reflejar la actualización
            document.getElementById('createRow').remove(); // Eliminar la fila de creación de telefono
            document.getElementById('createButton').style.display = 'inline-block'; // Mostrar el botón de crear
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
            deleteTelefono(response.id).then(data => {
                if (!data.success) {
                    showMessage(data.message, 'error');
                    return;
                }
            });
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al añadirle el teléfono al usuario.<br>${error}`, 'error');
        deleteTelefono(response.id).then(data => {
            if (!data.success) {
                showMessage(data.message, 'error');
                return;
            } 
        });
    });
}

function removeTelefonoFromUsuario(telefonoID) {
    // Solicitar confirmación al usuario antes de eliminar el telefono
    if (confirm('¿Estás seguro de que deseas eliminar este telefono?')) {
        // Obtener el id del usuario
        const usuario = document.getElementById('usuario-select').value;
        const usuarioID = parseInt(usuario);

        // Crear un objeto para almacenar los datos a enviar al servidor
        let data = {
            accion: 'eliminar',
            usuario: usuarioID,
            telefono: telefonoID
        };

        // Enviar la solicitud POST al servidor con el id del telefono a eliminar
        fetch('../controller/usuarioTelefonoAction.php', {
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
                fetchTelefonos(usuarioID, currentPage, pageSize, sort); // Recargar los datos de telefonos para reflejar la actualización
            } else {
                // Mostrar mensaje de error
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar el telefono del usuario.<br>${error}`, 'error');
        });
    }
}