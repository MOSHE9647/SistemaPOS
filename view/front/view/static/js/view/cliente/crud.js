// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader, showLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { updateTelefono } from "../telefono/crud.js";
import { fetchClientes } from "./pagination.js";

async function createTelefono(formData) {
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'insertar' };

    // Obtener los datos del teléfono
    const telefonoData = formData;
    for (const [key, value] of Object.entries(telefonoData)) {
        data[key] = value; // Agregar cada propiedad de teléfono al objeto `data`
    }

    try {
        // Enviar la solicitud POST al servidor
        const response = await fetch('../../../../../../controller/telefonoAction.php', {
            method: 'POST',
            body: new URLSearchParams(data),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        return await response.json();
    } catch (error) {
        // Manejar errores en la solicitud o en el procesamiento de la respuesta
        return { message: `Ocurrió un error al crear el teléfono para el cliente: ${error.message}` };
    }
}

async function deleteTelefono(id) {
    try {
        // Enviar la solicitud POST al servidor con el id del telefono a eliminar
        const response = await fetch('../../../../../../controller/telefonoAction.php', {
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

/**
 * Crea un nuevo cliente enviando una solicitud POST al servidor.
 * 
 * @description Esta función recopila los datos de los campos de entrada en el elemento #createRow.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito, recarga los datos de clientes y elimina la fila de creación.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * insertCliente();
 * 
 * @returns {void}
 */
export async function insertCliente(formData) {
    // Intentar crear el teléfono
    const response = await createTelefono(formData[1]);

    // Si la creación del teléfono falla, mostrar un mensaje de error
    if (!response.success) {
        mostrarMensaje(response.message, 'error', 'Error al crear el teléfono');
        return;
    }

    // Si el teléfono ya existe, preguntar al usuario si desea actualizarlo
    const telefonoID = response.id;
    if (response.inactive) {
        if (confirm(response.message)) { 
            formData[1].id = telefonoID;
            updateTelefono(formData, true);
        }
        else { 
            mostrarMensaje('No se creó el cliente.', 'info', 'Creación cancelada'); 
            return;
        }
    }

    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'insertar' };

    // Obtener los datos del cliente
    const dataCliente = formData[0];
    for (const [key, value] of Object.entries(dataCliente)) {
        data[key] = value; // Agregar cada propiedad de cliente al objeto `data`
    }
    data['telefono'] = telefonoID; // Agregar el ID del teléfono al objeto `data`

    // Enviar la solicitud POST al servidor
    fetch('../../../../../../controller/clienteAction.php', {
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
            mostrarMensaje(data.message, 'success'); // Mostrar mensaje de éxito
            fetchClientes(window.currentPage, window.pageSize, window.sort); // Recargar los datos de clientes para reflejar la creación
        } else {
            // Mostrar mensaje de error
            mostrarMensaje(data.message, 'error', 'Error al crear');
            deleteTelefono(telefonoID).then(data => {
                if (!data.success) {
                    showMessage(data.message, 'error');
                    return;
                }
            });
        }
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el nuevo cliente.<br>${error}`, 'error', 'Error al crear');
        deleteTelefono(telefonoID).then(data => {
            if (!data.success) {
                showMessage(data.message, 'error');
                return;
            }
        });
    });
}

export function updateCliente(formData) {
    // Crear un objeto para almacenar los datos a enviar al servidor
    let data = { accion: 'actualizar' };

    // Obtener los datos del cliente
    const dataCliente = formData[0];
    for (const [key, value] of Object.entries(dataCliente)) {
        data[key] = value; // Agregar cada propiedad de cliente al objeto `data`
    }

    // Enviar la solicitud POST al servidor
    fetch('../../../../../../controller/clienteAction.php', {
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
            // Intentar actualizar el cliente
            updateTelefono(formData[1], true);
            mostrarMensaje(data.message, 'success'); // Mostrar mensaje de éxito
            fetchClientes(window.currentPage, window.pageSize, window.sort); // Recargar los datos de clientes para reflejar la creación
        } else {
            // Mostrar mensaje de error
            mostrarMensaje(data.message, 'error', 'Error al actualizar');
        }
        hideLoader(); // Ocultar el loader
    })
    .catch(error => {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el nuevo cliente.<br>${error}`, 'error', 'Error al actualizar');
        hideLoader(); // Ocultar el loader
    });
}

export function deleteCliente(id) {
    // Solicitar confirmación al usuario antes de eliminar el cliente
    if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
        showLoader(); // Mostrar el loader

        // Enviar la solicitud POST al servidor con el id del cliente a eliminar
        fetch('../../../../../../controller/clienteAction.php', {
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
                mostrarMensaje(data.message, 'success', 'Cliente eliminado');
                
                // Recargar los datos de clientes para reflejar la eliminación
                fetchClientes(window.currentPage, window.pageSize, window.sort);
            } else {
                // Mostrar mensaje de error
                mostrarMensaje(data.message, 'error', 'Error al eliminar');
            }
            
            hideLoader(); // Ocultar el loader
        })
        .catch(error => {
            // Mostrar mensaje de error detallado
            mostrarMensaje(`Ocurrió un error al eliminar el cliente.<br>${error}`, 'error', 'Error al eliminar');
            hideLoader(); // Ocultar el loader
        });
    }
}