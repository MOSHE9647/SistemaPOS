// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader, showLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { fetchRoles } from "./pagination.js";

/**
 * Obtiene un rol por su ID.
 *
 * @async
 * @function obtenerRolPorID
 * @param {number} id - El ID del rol a obtener.
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención del rol.
 * @param {boolean} [deleted=false] - Si se deben incluir roles eliminados en la obtención.
 * @returns {Promise<Object>} Los datos del rol si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o el rol no se encuentra.
 */
async function obtenerRolPorID(id, filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const response = await fetch(
        `${window.baseURL}/controller/rolUsuarioAction.php?accion=id&id=${id}&filter=${filterNum}&deleted=${deletedNum}`
    );
    const data = await response.json();
    if (data.success) {
        return data.rol;
    } else {
        throw new Error(data.message);
    }
}

/**
 * Inserta un nuevo rol enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario recibidos en el parámetro 'formData' al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los roles.
 *              Si el rol ya existe pero está inactivo, pregunta al usuario si desea actualizarlo.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * insertRol(formData);
 * 
 * @param {Object} formData - Los datos del formulario del rol.
 * @returns {void}
 */
export async function insertRol(formData) {
    showLoader(); // Mostrar el loader

    try {
        // Enviar la solicitud POST al servidor con los datos del rol
        const response = await fetch(`${window.baseURL}/controller/rolUsuarioAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al crear el rol');
            return; // Salir de la función si hay un error
        }

        // Si el rol está inactivo, preguntar al usuario si desea actualizarlo
        if (data.inactive) {
            const confirmacion = confirm(data.message);
            if (!confirmacion) {
                mostrarMensaje('Se canceló la creación del rol', 'info', 'Creación cancelada');
                return; // Salir de la función si se cancela la actualización
            }

            // Intentar actualizar el rol existente
            const rol = await obtenerRolPorID(parseInt(data.id), true, true);
            formData.set('accion', 'actualizar');
            formData.append('id', rol.id);

            // Actualizar el rol existente
            updateRol(formData);
            return; // Salir de la función
        }

        // Mostrar mensaje de éxito y recargar los datos de los roles
        mostrarMensaje(data.message, 'success');
        fetchRoles(window.currentPage, window.pageSize, window.sort);
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el nuevo rol.<br>${error}`, 'error', 'Error al crear el rol');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Actualiza un rol enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario recibidos en el parámetro 'formData' al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los roles.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * updateRol(formData);
 * 
 * @param {Object} formData - Los datos del formulario del rol.
 * @returns {void}
 */
export async function updateRol(formData) {
    showLoader(); // Mostrar el loader
    
    try {
        // Enviar la solicitud POST al servidor con los datos del rol
        const response = await fetch(`${window.baseURL}/controller/rolUsuarioAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al actualizar el rol');
            return; // Salir de la función si hay un error
        }

        // Mostrar mensaje de éxito y recargar los datos de los roles
        mostrarMensaje(data.message, 'success');
        fetchRoles(window.currentPage, window.pageSize, window.sort);
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al actualizar el rol.<br>${error}`, 'error', 'Error al actualizar el rol');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Elimina un rol enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de enviar la solicitud de eliminación.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los roles.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * deleteRol(id);
 * 
 * @param {number} id - El ID del rol a eliminar.
 * @returns {void}
 */
export async function deleteRol(id) {
    // Solicitar confirmación al usuario antes de eliminar el rol
    const confirmacion = confirm('¿Está seguro de que desea eliminar el rol seleccionado?');
    if (!confirmacion) {
        mostrarMensaje('Se canceló la eliminación del rol', 'info', 'Eliminación cancelada');
        return; // Salir de la función si se cancela la eliminación
    }
    showLoader(); // Mostrar el loader

    try {
        // Enviar la solicitud POST al servidor con el id del rol a eliminar
        const response = await fetch(`${window.baseURL}/controller/rolUsuarioAction.php`, {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        });
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al eliminar el rol');
            return; // Salir de la función si hay un error
        }

        // Mostrar mensaje de éxito y recargar los datos de los roles
        mostrarMensaje(data.message, 'success');
        fetchRoles(window.currentPage, window.pageSize, window.sort);
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al eliminar el rol.<br>${error}`, 'error', 'Error al eliminar el rol');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}