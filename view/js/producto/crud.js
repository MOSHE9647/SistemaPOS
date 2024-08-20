// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo producto enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en el elemento #createRow,
 *              convierte el campo 'precio' a un número decimal con 2 lugares decimales y los envía al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito, recarga los datos de productos y elimina la fila de creación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * // Asumiendo que el elemento #createRow tiene la siguiente estructura:
 * // <tr id="createRow">
 * //   <td data-field="nombre"><input type="text" value="Nuevo Producto"></td>
 * //   <td data-field="precio"><input type="number" value="10.50"></td>
 * // </tr>
 * createProducto();
 * 
 * @returns {void}
 */
function createProducto() {
    // Obtener la fila de creación de producto
    let row = document.getElementById('createRow');
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'insertar' };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o precio)
        let fieldName = input.closest('td').dataset.field;
        
        // Obtener el precio del campo
        let value = input.value;

        // Convertir el campo 'precio' a un número decimal con 2 lugares decimales
        if (fieldName === 'precio') {
            value = parseFloat(value).toFixed(2); // Convertir a número decimal y limitar a 2 decimales
        }

        // Agregar el campo al objeto de datos
        data[fieldName] = value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/productoAction.php', {
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
            
            // Recargar los datos de productos para reflejar la creación del nuevo producto
            fetchProductos(currentPage, pageSize, sort);
            
            // Eliminar la fila de creación de producto
            document.getElementById('createRow').remove();
            
            // Mostrar el botón de creación de producto nuevamente
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al crear el nuevo producto.<br>${error}`, 'error');
    });
}

/**
 * Actualiza un producto existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en la fila con el id especificado,
 *              convierte el campo 'precio' a un número decimal con 2 lugares decimales y los envía al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de productos para reflejar la actualización.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del producto a actualizar
 * 
 * @example
 * updateProducto(1); // Actualizar el producto con id 1
 * 
 * @returns {void}
 */
function updateProducto(id) {
    // Obtener la fila del producto con el id especificado
    let row = document.querySelector(`tr[data-id='${id}']`);
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'actualizar', id: id };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o precio)
        let fieldName = input.closest('td').dataset.field;
        
        // Obtener el precio del campo
        let value = input.value;

        // Convertir el campo 'precio' a un número decimal con 2 lugares decimales
        if (fieldName === 'precio') {
            value = parseFloat(value).toFixed(2); // Convertir a número decimal y limitar a 2 decimales
        }

        // Agregar el campo al objeto de datos
        data[fieldName] = value;
    });

    // Enviar la solicitud POST al servidor
    fetch('../controller/productoAction.php', {
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
            
            // Recargar los datos de productos para reflejar la actualización
            fetchProductos(currentPage, pageSize, sort);
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar el producto.<br>${error}`, 'error');
    });
}

/**
 * Elimina un producto existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de eliminar el producto.
 *              Si el usuario confirma, envía una solicitud POST al servidor con el id del producto a eliminar.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de productos para reflejar la eliminación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id del producto a eliminar
 * 
 * @example
 * deleteProducto(1); // Eliminar el producto con id 1
 * 
 * @returns {void}
 */
function deleteProducto(id) {
    // Solicitar confirmación al usuario antes de eliminar el producto
    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        // Enviar la solicitud POST al servidor con el id del producto a eliminar
        fetch('../controller/productoAction.php', {
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
                
                // Recargar los datos de productos para reflejar la eliminación
                fetchProductos(currentPage, pageSize, sort);
            } else {
                // Mostrar mensaje de error
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar el producto.<br>${error}`, 'error');
        });
    }
}