// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader, showLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { fetchClientes } from "./pagination.js";
import { resetSearch } from "../../utils.js";

/**
 * Obtiene un cliente por su ID.
 *
 * @async
 * @function obtenerClientePorID
 * @param {number} id - El ID del cliente a obtener.
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención del cliente.
 * @param {boolean} [deleted=false] - Si se deben incluir clientes eliminados en la obtención.
 * @returns {Promise<Object>} Los datos del cliente si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o el cliente no se encuentra.
 */
async function obtenerClientePorID(id, filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const response = await fetch(
        `${window.baseURL}/controller/clienteAction.php?accion=id&id=${id}&filter=${filterNum}&deleted=${deletedNum}`
    );
    const data = await response.json();
    if (data.success) {
        return data.cliente;
    } else {
        throw new Error(data.message);
    }
}

/**
 * Crea un nuevo cliente enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario del cliente al servidor para crear un nuevo cliente.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los clientes.
 *              Si la solicitud falla, muestra un mensaje de error. Si el cliente ya existe pero está inactivo,
 *              pregunta al usuario si desea actualizarlo.
 * 
 * @example
 * insertCliente(formData);
 * 
 * @param {Object} formData - Los datos del formulario del cliente.
 * @returns {void}
 */
export async function insertCliente(formData) {
    showLoader(); // Mostrar el loader
    
    try {
        // Enviar la solicitud POST al servidor con los datos del cliente
        const response = await fetch(`${window.baseURL}/controller/clienteAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el cliente');
        const data = await response.json();
        
        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al crear el cliente');
            return; // Salir de la función si hay error
        }

        // Si el cliente está inactivo, preguntar al usuario si desea actualizarlo
        if (data.inactive) {
            const confirmacion = confirm(data.message);
            if (!confirmacion) {
                mostrarMensaje("Se canceló la creación del cliente", 'info', 'Creación cancelada');
                return; // Salir de la función si se cancela la actualización
            }

            // Intentar actualizar el cliente existente
            const cliente = await obtenerClientePorID(parseInt(data.id), true, true);
            formData.set('accion', 'actualizar');
            formData.append('id', cliente.ID);
            formData.append('telefono', cliente.Telefono.ID);
            updateCliente(formData); // Actualizar el cliente
            return; // Salir de la función
        }

        // Mostrar mensaje de éxito y recargar los clientes
        mostrarMensaje(data.message, 'success');
        fetchClientes(window.currentPage, window.pageSize, window.sort);
        resetSearch('cliente-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el cliente.<br>${error}`, 'error', 'Error al crear el cliente');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Actualiza un cliente enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario del cliente al servidor para actualizar un cliente existente.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los clientes.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * updateCliente(formData);
 * 
 * @param {Object} formData - Los datos del formulario del cliente.
 * @returns {void}
 */
export async function updateCliente(formData) {
    showLoader(); // Mostrar el loader
    
    try {
        // Enviar la solicitud POST al servidor con los datos del cliente
        const response = await fetch(`${window.baseURL}/controller/clienteAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al actualizar el cliente');
            return; // Salir de la función si hay error
        }

        // Mostrar mensaje de éxito y recargar los clientes
        mostrarMensaje(data.message, 'success');
        fetchClientes(window.currentPage, window.pageSize, window.sort);
        resetSearch('cliente-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al actualizar el cliente.<br>${error}`, 'error', 'Error al actualizar el cliente');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Elimina un cliente enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de enviar la solicitud de eliminación.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los clientes.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * deleteCliente(id);
 * 
 * @param {number} id - El ID del cliente a eliminar.
 * @returns {void}
 */
export async function deleteCliente(id) {
    // Solicitar confirmación al usuario antes de eliminar el cliente
    const confirmacion = confirm('¿Estás seguro de que deseas eliminar este cliente?');
    if (!confirmacion) {
        mostrarMensaje("Se canceló la eliminación del cliente", 'info', 'Eliminación cancelada');
        return; // Salir de la función si se cancela la eliminación
    }
    showLoader(); // Mostrar el loader

    try {
        // Enviar la solicitud POST al servidor con el id del cliente a eliminar
        const response = await fetch(`${window.baseURL}/controller/clienteAction.php`, {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al eliminar el cliente');
            return; // Salir de la función si hay error
        }

        // Mostrar mensaje de éxito y recargar los clientes
        mostrarMensaje(data.message, 'success');
        fetchClientes(window.currentPage, window.pageSize, window.sort);
        resetSearch('cliente-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al eliminar el cliente.<br>${error}`, 'error', 'Error al eliminar el cliente');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}