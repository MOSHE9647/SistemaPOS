// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Crea un nuevo subcategoria enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en el elemento #createRow.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito, recarga los datos de subcategorias y elimina la fila de creación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * createSubcategoria();
 * 
 * @returns {void}
 */
async function createSubcategoria() {
    // Obtener la fila de creación de subcategoria
    const row = document.getElementById('createRow');
    
    if (!row) {
        console.error('No se encontró la fila de creación de subcategoría.');
        return { message: 'No se encontró la fila de creación de subcategoría.' };
    }

    // Obtener los campos de entrada de la fila
    const inputs = row.querySelectorAll('input');
    
    // Crear un objeto para almacenar los datos a enviar al servidor
    const data = { accion: 'insertar' };

    // Recorrer cada campo de entrada y agregarlo al objeto de datos
    inputs.forEach(input => {
        // Obtener el nombre del campo (nombre o valor)
        const fieldName = input.closest('td').dataset.field;

        // Agregar el campo al objeto de datos
        data[fieldName] = input.value;
    });

    try {
        // Enviar la solicitud POST al servidor
        const response = await fetch('../controller/subcategoriaAction.php', {
            method: 'POST',
            body: new URLSearchParams(data),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al crear la nueva subcategoría: ${error.message}` };
    }
}

/**
 * Actualiza una subcategoria existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en la fila con el id especificado.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de subcategorias para reflejar la actualización.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id de la subcategoria a actualizar
 * 
 * @example
 * updateSubcategoria(1); // Actualizar la subcategoria con id 1
 * 
 * @returns {void}
 */
function updateSubcategoria(id) {
    // Obtener la fila de la subcategoria con el id especificado
    let row = document.querySelector(`tr[data-id='${id}']`);
    
    // Obtener los campos de entrada de la fila
    let inputs = row.querySelectorAll('input');
    
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
    fetch('../controller/subcategoriaAction.php', {
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
            
            // Obtener el id del producto
            const producto = document.getElementById('producto-select').value;
            const productoID = parseInt(producto);

            // Recargar los datos de subcategorias para reflejar la actualización
            fetchSubcategorias(productoID, currentPage, pageSize, sort);
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al actualizar la subcategoria.<br>${error}`, 'error');
    });
}

/**
 * Elimina una subcategoria existente enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de eliminar la subcategoria.
 *              Si el usuario confirma, envía una solicitud POST al servidor con el id de la subcategoria a eliminar.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de subcategorias para reflejar la eliminación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @param {number} id - El id de la subcategoria a eliminar
 * 
 * @example
 * deleteSubcategoria(1); // Eliminar la subcategoria con id 1
 * 
 * @returns {void}
 */
async function deleteSubcategoria(id) {
    try {
        // Enviar la solicitud POST al servidor con el id de la subcategoria a eliminar
        const response = await fetch('../controller/subcategoriaAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al eliminar la subcategoria.<br>${error}` };
    }
}

async function addSubcategoriaToProducto() {
    const response = await createSubcategoria();
    if (response.success) {
        console.log('Subcategoría creada con ID:', response.id);
    } else if (response.message) {
        console.error('Error al crear la subcategoría:', response.message);
        showMessage(response.message, 'error');
        return;
    }

    // Obtener el id del producto
    const producto = document.getElementById('producto-select').value;
    const productoID = parseInt(producto);

    // Crear un objeto para almacenar los datos a enviar al servidor
    let dataToSend = {
        accion: 'agregar',
        producto: productoID,
        subcategoria: response.id
    };

    // Enviar la solicitud POST al servidor
    fetch('../controller/productoSubcategoriaAction.php', {
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
            // Mostrar mensaje de éxito
            showMessage(data.message, 'success');
            
            // Recargar los datos de subcategorias para reflejar la creación dla nueva subcategoria
            fetchSubcategorias(productoID, currentPage, pageSize, sort);
            
            // Eliminar la fila de creación de subcategoria
            document.getElementById('createRow').remove();
            
            // Mostrar el botón de creación de subcategoria nuevamente
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            // Mostrar mensaje de error
            showMessage(data.message, 'error');
            deleteSubcategoria(response.id).then(data => {
                if (data.success) {
                    console.log('Subcategoría eliminada después del fallo');
                } else if (data.message) {
                    console.error('Error al eliminar la subcategoría:', data.message);
                    showMessage(data.message, 'error');
                    return;
                }
            });
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        showMessage(`Ocurrió un error al añadirle la subcategoría al producto.<br>${error}`, 'error');
        deleteSubcategoria(response.id).then(data => {
            if (data.success) {
                console.log('Subcategoría eliminada después del fallo');
            } else if (data.message) {
                console.error('Error al eliminar la subcategoría:', data.message);
                showMessage(data.message, 'error');
                return;
            }
        });
    });
}

function removeSubcategoriaFromProducto(subcategoriaID) {
    // Solicitar confirmación al usuario antes de eliminar la subcategoria
    if (confirm('¿Estás seguro de que deseas eliminar esta subcategoria?')) {
        // Obtener el id del producto
        const producto = document.getElementById('producto-select').value;
        const productoID = parseInt(producto);

        // Crear un objeto para almacenar los datos a enviar al servidor
        let data = {
            accion: 'eliminar',
            producto: productoID,
            subcategoria: subcategoriaID
        };

        // Enviar la solicitud POST al servidor con el id de la subcategoria a eliminar
        fetch('../controller/productoSubcategoriaAction.php', {
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

                // Recargar los datos de subcategorias para reflejar la eliminación
                fetchSubcategorias(productoID, currentPage, pageSize, sort);
            } else {
                // Mostrar mensaje de error
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            showMessage(`Ocurrió un error al eliminar la subcategoria del producto.<br>${error}`, 'error');
        });
    }
}