// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader, showLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { fetchCategorias } from "./pagination.js";

/**
 * Obtiene una categoría por su ID.
 *
 * @async
 * @function obtenerCategoriaPorID
 * @param {number} id - El ID de la categoría a obtener.
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención de la categoría.
 * @param {boolean} [deleted=false] - Si se deben incluir categorías eliminadas en la obtención.
 * @returns {Promise<Object>} Los datos de la categoría si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o la categoría no se encuentra.
 */
async function obtenerCategoriaPorID(id, filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const response = await fetch(
        `${window.baseURL}/controller/categoriaAction.php?accion=id&id=${id}&filter=${filterNum}&deleted=${deletedNum}`
    );
    const data = await response.json();
    if (data.success) {
        return data.categoria;
    } else {
        throw new Error(data.message);
    }
}

/**
 * Inserta una nueva categoría enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario recibidos en el parámetro 'formData' al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de las categorías.
 *              Si la categoría ya existe pero está inactiva, pregunta al usuario si desea actualizarla.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * insertCategoria(formData);
 * 
 * @param {Object} formData - Los datos del formulario de la categoría.
 * @returns {void}
 */
export async function insertCategoria(formData) {
    showLoader(); // Mostrar el loader
    
    try {
        // Enviar la solicitud POST al servidor con los datos de la categoría
        const response = await fetch(`${window.baseURL}/controller/categoriaAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al crear la categoría');
            return; // Salir de la función si hay un error
        }

        // Si la categoría está inactiva, preguntar al usuario si desea actualizarla
        if (data.inactive) {
            const confirmacion = confirm(data.message);
            if (!confirmacion) {
                mostrarMensaje('Se canceló la creación de la categoría', 'info', 'Creación cancelada');
                return; // Salir de la función si se cancela la actualización
            }

            // Intentar actualizar la categoría existente
            const categoria = await obtenerCategoriaPorID(parseInt(data.id), true, true);
            formData.set('accion', 'actualizar');
            formData.append('id', categoria.id);

            // Actualizar la categoría existente
            updateCategoria(formData);
            return; // Salir de la función
        }

        // Mostrar mensaje de éxito y recargar los datos de las categorías
        mostrarMensaje(data.message, 'success');
        fetchCategorias(window.currentPage, window.pageSize, window.sort);
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear la nueva categoría.<br>${error}`, 'error', 'Error al crear la categoría');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Actualiza una categoría enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario recibidos en el parámetro 'formData' al servidor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de las categorías.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * updateCategoria(formData);
 * 
 * @param {Object} formData - Los datos del formulario de la categoría.
 * @returns {void}
 */
export async function updateCategoria(formData) {
    showLoader(); // Mostrar el loader
    
    try {
        // Enviar la solicitud POST al servidor con los datos de la categoría
        const response = await fetch(`${window.baseURL}/controller/categoriaAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al actualizar la categoría');
            return; // Salir de la función si hay un error
        }

        // Mostrar mensaje de éxito y recargar los datos de las categorías
        mostrarMensaje(data.message, 'success');
        fetchCategorias(window.currentPage, window.pageSize, window.sort);
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al actualizar la categoría.<br>${error}`, 'error', 'Error al actualizar la categoría');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Elimina una categoría enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de enviar la solicitud de eliminación.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de las categorías.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * deleteCategoria(id);
 * 
 * @param {number} id - El ID de la categoría a eliminar.
 * @returns {void}
 */
export async function deleteCategoria(id) {
    // Solicitar confirmación al usuario antes de eliminar la categoría
    const confirmacion = confirm('¿Está seguro de que desea eliminar la categoría seleccionada?');
    if (!confirmacion) {
        mostrarMensaje('Se canceló la eliminación de la categoría', 'info', 'Eliminación cancelada');
        return; // Salir de la función si se cancela la eliminación
    }
    showLoader(); // Mostrar el loader

    try {
        // Enviar la solicitud POST al servidor con el id de la categoría a eliminar
        const response = await fetch(`${window.baseURL}/controller/categoriaAction.php`, {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al eliminar la categoría');
            return; // Salir de la función si hay un error
        }

        // Mostrar mensaje de éxito y recargar los datos de las categorías
        mostrarMensaje(data.message, 'success');
        fetchCategorias(window.currentPage, window.pageSize, window.sort);
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al eliminar la categoría.<br>${error}`, 'error', 'Error al eliminar la categoría');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}