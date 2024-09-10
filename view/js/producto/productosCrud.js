// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo lote.
 * 
 * @description Envía una solicitud POST al servidor para crear un nuevo lote con los datos ingresados en la fila de creación.
 * @example
 * createLote();
 */
function createProducto() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;
  
        data[fieldName] = value;
    });
    console.log('Datos enviados para crear producto:', data); // Mensaje de depuración

    fetch('../controller/productoAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor al crear producto:', data); // Mensaje de depuración
        if (data.success) {
            showMessage(data.message, 'success');
            fetchProductos(currentPage, pageSize);
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al crear el nuevo lote.<br>${error}`, 'error');
    });
}

/**
 * Actualiza un lote existente.
 * 
 * @param {number} id - El ID del lote que se desea actualizar.
 * @description Envía una solicitud POST al servidor para actualizar el lote con los datos ingresados en la fila de edición.
 * @example
 * updateLote(123);
 */

function updateProducto(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;



        data[fieldName] = value;
    });

    console.log('Datos enviados:', data); // Para depuración

    fetch('../controller/productoAction.php', {
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
            fetchProductos(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al actualizarrr el producto.<br>${error}`, 'error');
    });
}

/**
 * Elimina un lote existente.
 * 
 * @param {number} id - El ID del lote que se desea eliminar.
 * @description Envía una solicitud POST al servidor para eliminar el lote después de confirmar con el usuario.
 * @example
 * deleteLote(123);
 */
function deleteProducto(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este lote?')) {
        fetch('../controller/productoAction.php', {
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
                fetchProductos(currentPage, pageSize); // Recargar datos para reflejar la eliminación del lote
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Muestra el mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar el producto.<br>${error}`, 'error');
        });
    }
}