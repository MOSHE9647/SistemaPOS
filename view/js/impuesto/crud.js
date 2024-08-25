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
    // Obtener la fila de creación de impuesto
    let row = document.getElementById('createRow');
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'insertar' };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o valor)
        let fieldName = input.closest('td').dataset.field;
        
        // Obtener el valor del campo
        let value = input.value;

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
            // Mostrar mensaje de éxito
            showMessage(data.message, 'success');
            
            // Recargar los datos de impuestos para reflejar la creación del nuevo impuesto
            fetchImpuestos(currentPage, pageSize, sort);
            
            // Eliminar la fila de creación de impuesto
            document.getElementById('createRow').remove();
            
            // Mostrar el botón de creación de impuesto nuevamente
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
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
function updateImpuesto(id) {
    // Obtener la fila del impuesto con el id especificado
    let row = document.querySelector(`tr[data-id='${id}']`);
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'actualizar', id: id };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o valor)
        let fieldName = input.closest('td').dataset.field;
        
        // Obtener el valor del campo
        let value = input.value;

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
            // Mostrar mensaje de éxito
            showMessage(data.message, 'success');
            
            // Recargar los datos de impuestos para reflejar la actualización
            fetchImpuestos(currentPage, pageSize, sort);
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
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
                // Mostrar mensaje de éxito
                showMessage(data.message, 'success');
                
                // Recargar los datos de impuestos para reflejar la eliminación
                fetchImpuestos(currentPage, pageSize, sort);
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