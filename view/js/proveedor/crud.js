// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo proveedor enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en el elemento #createRow.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito, recarga los datos de proveedors y elimina la fila de creación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * // Asumiendo que el elemento #createRow tiene la siguiente estructura:
 * // <tr id="createRow">
 * //   <td data-field="nombre"><input type="text" value="Nuevo Proveedor"></td>
 * //   <td data-field="email"><input type="email" value="proveedor@ejemplo.com"></td>
 * // </tr>
 * createProveedor();
 * 
 * @returns {void}
 */
function createProveedor() {
    // Obtener la fila de creación de proveedor
    let row = document.getElementById('createRow');
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input, select');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'insertar' };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o valor)
        let fieldName = input.closest('td').dataset.field;
        
        // Obtener el valor del campo
        let value = input.value;

        // Agregar el campo al objeto de datos
        data[fieldName] = value;
    });

    console.log(data);

    // Enviar la solicitud POST al servidor
    fetch('../controller/proveedorAction.php', {
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
                fetchProveedores(currentPage, pageSize, sort); // Recargar los datos de proveedores para reflejar la creación
                document.getElementById('createRow').remove(); // Eliminar la fila de creación
                document.getElementById('createButton').style.display = 'inline-block'; // Mostrar el botón de crear
                return;
            }

            // Actualizar el proveedor con los nuevos datos
            if (data.inactive && confirm(data.message)) { updateProveedor(data.id, true); }
            else { showMessage('No se agregó el proveedor', 'info'); }
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al crear el nuevo proveedor.<br>${error}`, 'error');
    });
}

/**
 * Actualiza un proveedor existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en la fila con el id especificado.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de proveedors para reflejar la actualización.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del proveedor a actualizar
 * 
 * @example
 * updateProveedor(1); // Actualizar el proveedor con id 1
 * 
 * @returns {void}
 */
function updateProveedor(id, reactivate = false) {
    let row;
    if (!reactivate) {
        row = document.querySelector(`tr[data-id='${id}']`); //<- Obtener la fila de la tabla con el id especificado
    } else {
        row = document.getElementById('createRow'); //<- Obtener la fila de creación de proveedor
    }

    // Si no se encuentra la fila, salir de la función
    if (!row) {
        showMessage('No se encontró la fila del proveedor a actualizar', 'error'); 
        return; 
    }
    let inputs = row.querySelectorAll('input, select'); // Obtener los campos de entrada de la fila
    let data = { accion: 'actualizar', id: id }; // Crear un objeto para almacenar los datos a enviar al servidor

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o valor)
        let fieldName = input.closest('td').dataset.field;
        
        // Obtener el valor del campo
        let value = input.value;

        // Agregar el campo al objeto de datos
        data[fieldName] = value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/proveedorAction.php', {
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
            
            // Recargar los datos de proveedors para reflejar la actualización
            fetchProveedores(currentPage, pageSize, sort);
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar el proveedor.<br>${error}`, 'error');
    });
}

/**
 * Elimina un proveedor existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al proveedor antes de eliminar el proveedor.
 *              Si el proveedor confirma, envía una solicitud POST al servidor con el id del proveedor a eliminar.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de proveedors para reflejar la eliminación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del proveedor a eliminar
 * 
 * @example
 * deleteProveedor(1); // Eliminar el proveedor con id 1
 * 
 * @returns {void}
 */
function deleteProveedor(id) {
    // Solicitar confirmación al proveedor antes de eliminar el proveedor
    if (confirm('¿Estás seguro de que deseas eliminar este proveedor?')) {
        // Enviar la solicitud POST al servidor con el id del proveedor a eliminar
        fetch('../controller/proveedorAction.php', {
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
                
                // Recargar los datos de proveedors para reflejar la eliminación
                fetchProveedores(currentPage, pageSize, sort);
            } else {
                // Mostrar mensaje de error
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar el proveedor.<br>${error}`, 'error');
        });
    }

    
}