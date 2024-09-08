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
async function createDireccion() {
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

    try {
        let response = await fetch('../controller/direccionAction.php', {
            method: 'POST',
            body: new URLSearchParams(data),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al crear la nueva dirección.<br>${error.message}` };
    }
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

            const proveedor = document.getElementById('proveedor-select').value;
            const proveedorID = parseInt(proveedor);

            fetchDirecciones(proveedorID, currentPage, pageSize, sort); // Recargar datos para reflejar la actualización de la direccion
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
async function deleteDireccion(id) {
    try {
        let response = await fetch('../controller/direccionAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al eliminar la dirección.<br>${error.message}` };
    }
}

async function addDireccionToProveedor() {
    const response = await createDireccion();
    if (response.success) {
        console.log('Dirección creada con ID:', response.id);
    } else if (response.message) {
        console.error('Error al crear la dirección:', response.message);
        showMessage(response.message, 'error');
        return;
    }

    // Obtener el id del proveedor
    const proveedor = document.getElementById('proveedor-select').value;
    const proveedorID = parseInt(proveedor);

    // Crear un objeto para almacenar los datos a enviar al servidor
    let dataToSend = {
        accion: 'agregar',
        proveedor: proveedorID,
        direccion: response.id
    };

    // Enviar la solicitud POST al servidor
    fetch('../controller/proveedorDireccionAction.php', {
        method: 'POST',
        body: new URLSearchParams(dataToSend),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchDirecciones(proveedorID, currentPage, pageSize, sort);
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
            deleteDireccion(response.id).then(() => {
                if (data.success) {
                    console.log('Dirección eliminada después del fallo');
                } else if (data.message) {
                    console.error('Error al eliminar la dirección:', data.message);
                    showMessage(data.message, 'error');
                    return;
                }
            });
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al agregar la dirección al proveedor.<br>${error}`, 'error');
        deleteDireccion(response.id).then(() => {
            if (data.success) {
                console.log('Dirección eliminada después del fallo');
            } else if (data.message) {
                console.error('Error al eliminar la dirección:', data.message);
                showMessage(data.message, 'error');
                return;
            }
        });
    });
}

function removeDireccionFromProveedor(direccionID) {
    // Solicitar confirmación al usuario antes de eliminar la dirección
    if (!confirm('¿Está seguro de que desea eliminar esta dirección?')) {
        return;
    }

    // Obtener el id del proveedor
    const proveedor = document.getElementById('proveedor-select').value;
    const proveedorID = parseInt(proveedor);

    // Crear un objeto para almacenar los datos a enviar al servidor
    let dataToSend = {
        accion: 'eliminar',
        proveedor: proveedorID,
        direccion: direccionID
    };

    // Enviar la solicitud POST al servidor con el id de la dirección a eliminar
    fetch('../controller/proveedorDireccionAction.php', {
        method: 'POST',
        body: new URLSearchParams(dataToSend),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchDirecciones(proveedorID, currentPage, pageSize, sort);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al eliminar la dirección del proveedor.<br>${error}`, 'error');
    });
}