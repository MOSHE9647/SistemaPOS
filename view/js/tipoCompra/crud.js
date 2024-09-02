// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo tipo de compra enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en el elemento #createRow,
 *              convierte el campo 'tasaInteres' a un número decimal con 2 lugares decimales y los envía al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito, recarga los datos de tipoCompra y elimina la fila de creación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @returns {void}
 */
function createTipoCompra() {
    // Obtener la fila de creación de tipoCompra
    let row = document.getElementById('createRow');
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'insertar' };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre, tasaInteres, plazos, etc.)
        let fieldName = input.closest('td').dataset.field;
        
        // Obtener el valor del campo
        let value = input.value;

        // Convertir el campo 'tasaInteres' a un número decimal con 2 lugares decimales
        if (fieldName === 'tasaInteres') {
            value = parseFloat(value).toFixed(2); // Convertir a número decimal y limitar a 2 decimales
        }

        // Agregar el campo al objeto de datos
        data[fieldName] = value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/tipoCompraAction.php', {
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
            
            // Recargar los datos de tipoCompra para reflejar la creación del nuevo registro
            fetchTipoCompra(currentPage, pageSize, sort);
            
            // Eliminar la fila de creación de tipoCompra
            document.getElementById('createRow').remove();
            
            // Mostrar el botón de creación de tipoCompra nuevamente
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al crear el nuevo tipo de compra.<br>${error}`, 'error');
    });
}

/**
 * Actualiza un tipo de compra existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en la fila con el id especificado,
 *              convierte el campo 'tasaInteres' a un número decimal con 2 lugares decimales y los envía al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de tipoCompra para reflejar la actualización.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del tipo de compra a actualizar
 * 
 * @returns {void}
 */
function updateTipoCompra(id) {
    // Obtener la fila del tipoCompra con el id especificado
    let row = document.querySelector(`tr[data-id='${id}']`);
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'actualizar', id: id };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre, tasaInteres, plazos, etc.)
        let fieldName = input.closest('td').dataset.field;
        
        // Obtener el valor del campo
        let value = input.value;

        // Convertir el campo 'tasaInteres' a un número decimal con 2 lugares decimales
        if (fieldName === 'tasaInteres') {
            value = parseFloat(value).toFixed(2); // Convertir a número decimal y limitar a 2 decimales
        }

        // Agregar el campo al objeto de datos
        data[fieldName] = value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/tipoCompraAction.php', {
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
            
            // Recargar los datos de tipoCompra para reflejar la actualización
            fetchTipoCompra(currentPage, pageSize, sort);
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar el tipo de compra.<br>${error}`, 'error');
    });
}

/**
 * Elimina un tipo de compra existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de eliminar el tipo de compra.
 *              Si el usuario confirma, envía una solicitud POST al servidor con el id del tipo de compra a eliminar.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de tipoCompra para reflejar la eliminación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del tipo de compra a eliminar
 * 
 * @returns {void}
 */
function deleteTipoCompra(id) {
    // Solicitar confirmación al usuario antes de eliminar el tipo de compra
    if (confirm('¿Estás seguro de que deseas eliminar este tipo de compra?')) {
        // Enviar la solicitud POST al servidor con el id del tipo de compra a eliminar
        fetch('../controller/tipoCompraAction.php', {
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
                
                // Recargar los datos de tipoCompra para reflejar la eliminación
                fetchTipoCompra(currentPage, pageSize, sort);
            } else {
                // Mostrar mensaje de error
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar el tipo de compra.<br>${error}`, 'error');
        });
    }
}
