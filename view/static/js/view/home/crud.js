// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader, showLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { obtenerClientePorID } from "../cliente/crud.js";
import { verificarRespuestaJSON } from "../../utils.js";

/**
 * Obtiene un producto por su código de barras.
 *
 * @async
 * @function obtenerProductoPorCodigoBarras
 * @param {string} codigoBarras - El código de barras del producto a obtener.
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención del producto.
 * @param {boolean} [deleted=false] - Si se deben incluir productos eliminados en la obtención.
 * @returns {Promise<Object>} Los datos del producto si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o el producto no se encuentra.
 */
export async function obtenerProductoPorCodigoBarras(codigoBarras) {
    const response = await fetch(
        `${window.baseURL}/controller/productoAction.php?accion=codigo&codigo=${codigoBarras}`
    );

    if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al obtener el producto');
    const data = await verificarRespuestaJSON(response);
    
    if (data.success) {
        return data.producto;
    } else {
        throw new Error(data.message);
    }
}

export function deleteProducto(id, productos) {
    const index = productos.findIndex(data => data.producto.ID === parseInt(id));
    if (index === -1) {
        mostrarMensaje('No se encontró la dirección a eliminar', 'error', 'Error al eliminar la dirección');
        return;
    }

    productos.splice(index, 1);
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
        const data = await verificarRespuestaJSON(response);
        
        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al crear el cliente');
            return -1; // Salir de la función si hay error
        }

        // Si el cliente está inactivo, preguntar al usuario si desea actualizarlo
        if (data.inactive) {
            const confirmacion = confirm(data.message);
            if (!confirmacion) {
                mostrarMensaje("Se canceló la creación del cliente", 'info', 'Creación cancelada');
                return -1; // Salir de la función si se cancela la actualización
            }

            // Intentar actualizar el cliente existente
            const cliente = await obtenerClientePorID(parseInt(data.id), true, true);
            formData.set('accion', 'actualizar');
            formData.append('id', cliente.ID);
            formData.append('telefono', cliente.Telefono.ID);
            updateCliente(formData); // Actualizar el cliente
            return parseInt(data.id); // Salir de la función
        }

        // Mostrar mensaje de éxito y recargar los clientes
        mostrarMensaje(data.message, 'success');
        return parseInt(data.id);
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el cliente.<br>${error}`, 'error', 'Error al crear el cliente');
        return -1;
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
        const data = await verificarRespuestaJSON(response);

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al actualizar el cliente');
            return; // Salir de la función si hay error
        }

        // Mostrar mensaje de éxito y recargar los clientes
        mostrarMensaje(data.message, 'success');
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al actualizar el cliente.<br>${error}`, 'error', 'Error al actualizar el cliente');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}