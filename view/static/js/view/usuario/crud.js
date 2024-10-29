// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader, showLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { resetSearch } from "../../utils.js";
import { fetchUsuarios } from "./pagination.js";

/**
 * Obtiene un usuario por su ID.
 *
 * @async
 * @function obtenerUsuarioPorID
 * @param {number} id - El ID del usuario a obtener.
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención del usuario.
 * @param {boolean} [deleted=false] - Si se deben incluir usuarios eliminados en la obtención.
 * @returns {Promise<Object>} Los datos del usuario si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o el usuario no se encuentra.
 */
async function obtenerUsuarioPorID(id, filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const response = await fetch(
        `${window.baseURL}/controller/usuarioAction.php?accion=id&id=${id}&filter=${filterNum}&deleted=${deletedNum}`
    );
    const data = await response.json();
    if (data.success) {
        return data.usuario;
    } else {
        throw new Error(data.message);
    }
}

/**
 * Inserta un nuevo usuario enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario del usuario al servidor para crear un nuevo registro.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los usuarios.
 *              Si la solicitud falla, muestra un mensaje de error.
 *              Si el usuario ya existe pero está inactivo, pregunta al usuario si desea actualizarlo.
 * 
 * @example
 * insertUsuario(formData);
 * 
 * @param {FormData} formData - Los datos del formulario del usuario.
 * @returns {Promise<void>}
 */
export async function insertUsuario(formData) {
    showLoader(); // Mostrar el loader
    
    try {
        // Enviar la solicitud POST al servidor con los datos del usuario
        const response = await fetch(`${window.baseURL}/controller/usuarioAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al crear el usuario');
            return; // Salir de la función si hay un error
        }

        // Si el usuario está inactivo, preguntar al usuario si desea actualizarlo
        if (data.inactive) {
            const confirmacion = confirm(data.message);
            if (!confirmacion) {
                mostrarMensaje('Se canceló la creación del usuario', 'info', 'Creación cancelada');
                return; // Salir de la función si se cancela la creación
            }

            // Intentar actualizar el usuario existente
            const usuario = await obtenerUsuarioPorID(parseInt(data.id), true, true);
            formData.set('accion', 'actualizar');
            formData.append('id', usuario.ID);
            updateUsuario(formData); // Actualizar el usuario
            return; // Salir de la función
        }

        // Mostrar mensaje de éxito y recargar los datos de usuarios
        mostrarMensaje(data.message, 'success');
        fetchUsuarios(window.currentPage, window.pageSize, window.sort);
        resetSearch('usuario-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el nuevo usuario.<br>${error}`, 'error', 'Error al crear el usuario');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Actualiza un usuario enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario del usuario al servidor para actualizar un registro existente.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los usuarios.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * updateUsuario(formData);
 * 
 * @param {FormData} formData - Los datos del formulario del usuario.
 * @returns {Promise<void>}
 */
export async function updateUsuario(formData) {
    showLoader(); // Mostrar el loader

    try {
        // Envía la solicitud POST al servidor con los datos del usuario
        const response = await fetch(`${window.baseURL}/controller/usuarioAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al actualizar el usuario');
            return; // Salir de la función si hay un error
        }

        // Mostrar mensaje de éxito y recargar los datos de usuarios
        mostrarMensaje(data.message, 'success');
        fetchUsuarios(window.currentPage, window.pageSize, window.sort);
        resetSearch('usuario-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al actualizar el usuario.<br>${error}`, 'error', 'Error al actualizar el usuario');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Elimina un usuario enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de enviar la solicitud de eliminación.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los usuarios.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * deleteUsuario(id);
 * 
 * @param {number} id - El ID del usuario a eliminar.
 * @returns {void}
 */
export async function deleteUsuario(id) {
    // Solicitar confirmación al usuario antes de eliminar el usuario
    const confirmacion = confirm('¿Estás seguro de que deseas eliminar este usuario?');
    if (!confirmacion) {
        mostrarMensaje('Se canceló la eliminación del usuario', 'info', 'Eliminación cancelada');
        return; // Salir de la función si se cancela la eliminación
    }
    showLoader(); // Mostrar el loader

    try {
        // Enviar la solicitud POST al servidor con el ID del usuario a eliminar
        const response = await fetch(`${window.baseURL}/controller/usuarioAction.php`, {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al eliminar el usuario');
            return; // Salir de la función si hay un error
        }

        // Mostrar mensaje de éxito y recargar los datos de usuarios
        mostrarMensaje(data.message, 'success');
        fetchUsuarios(window.currentPage, window.pageSize, window.sort);
        resetSearch('usuario-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al eliminar el usuario.<br>${error}`, 'error', 'Error al eliminar el usuario');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}