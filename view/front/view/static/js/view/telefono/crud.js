// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";

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
export function createTelefono(formData) {
    // Crear un objeto para almacenar los datos a enviar al servidor
    let dataToSend = { accion: 'insertar' };

    // Obtener los datos del teléfono
    const telefonoData = formData;
    for (const [key, value] of Object.entries(telefonoData)) {
        dataToSend[key] = value; // Agregar cada propiedad de teléfono al objeto `dataToSend`
    }

    // Enviar la solicitud POST al servidor
    fetch('../../../../../../controller/telefonoAction.php', {
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
            if (!data.inactive) {
                mostrarMensaje(data.message, 'success'); // Mostrar mensaje de éxito
                fetchTelefonos(window.currentPage, window.pageSize, window.sort); // Recargar los datos de telefonos para reflejar la creación
            }

            // Actualizar el telefono con los nuevos datos
            if (data.inactive && confirm(data.message)) { 
                formData['id'] = data.id;
                updateTelefono(formData, true);
            }
            else { mostrarMensaje('No se agregó el teléfono', 'info'); }
        } else {
            // Mostrar mensaje de error
            mostrarMensaje(data.message, 'error');
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el nuevo telefono.<br>${error}`, 'error');
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
export function updateTelefono(formData, external = false) {
    let data = { accion: 'actualizar' }; // Crear un objeto para almacenar los datos a enviar al servidor

    // Obtener los datos del teléfono
    const telefonoData = formData;
    for (const [key, value] of Object.entries(telefonoData)) {
        data[key] = value; // Agregar cada propiedad de teléfono al objeto `data`
    }

    // Enviar la solicitud POST al servidor
    fetch('../../../../../../controller/telefonoAction.php', {
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
            if (external) { return true; } 
            else {
                mostrarMensaje(data.message, 'success'); // Mostrar mensaje de éxito
                fetchTelefonos(window.currentPage, window.pageSize, window.sort); // Recargar los datos de telefonos para reflejar la actualización
            }
        } else {
            mostrarMensaje(data.message, 'error'); // Mostrar mensaje de error
            if (external) { return false; }
        }
        if (!external) hideLoader(); // Ocultar el loader
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al actualizar el telefono.<br>${error}`, 'error', 'Error interno');
        if (!external) hideLoader(); // Ocultar el loader
        if (external) { return false; }
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
export function deleteTelefono(id) {
    // Solicitar confirmación al usuario antes de eliminar el telefono
    if (confirm('¿Estás seguro de que deseas eliminar este telefono?')) {
        // Enviar la solicitud POST al servidor con el id del telefono a eliminar
        fetch('../../../../../../controller/telefonoAction.php', {
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
                mostrarMensaje(data.message, 'success');
                
                // Recargar los datos de telefonos para reflejar la eliminación
                fetchTelefonos(window.currentPage, window.pageSize, window.sort);
            } else {
                // Mostrar mensaje de error
                mostrarMensaje(data.message, 'error');
            }
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            mostrarMensaje(`Ocurrió un error al eliminar el telefono.<br>${error}`, 'error');
        });
    }
}