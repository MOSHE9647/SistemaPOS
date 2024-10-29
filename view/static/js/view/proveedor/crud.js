// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader, showLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { fetchProveedores } from "./pagination.js";
import { resetSearch } from "../../utils.js";

/**
 * Obtiene un proveedor por su ID.
 *
 * @async
 * @function obtenerProveedorPorID
 * @param {number} id - El ID del proveedor a obtener.
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención del proveedor.
 * @param {boolean} [deleted=false] - Si se deben incluir proveedores eliminados en la obtención.
 * @returns {Promise<Object>} Los datos del proveedor si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o el proveedor no se encuentra.
 */
async function obtenerProveedorPorID(id, filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const response = await fetch(
        `${window.baseURL}/controller/proveedorAction.php?accion=id&id=${id}&filter=${filterNum}&deleted=${deletedNum}`
    );
    const data = await response.json();
    if (data.success) {
        return data.proveedor;
    } else {
        throw new Error(data.message);
    }
}

/**
 * Crea un nuevo proveedor enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario del proveedor al servidor para crear un nuevo proveedor.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los proveedores.
 *              Si la solicitud falla, muestra un mensaje de error. Si el proveedor ya existe pero está inactivo,
 *              pregunta al usuario si desea actualizarlo.
 * 
 * @example
 * insertProveedor(formData);
 * 
 * @param {Object} formData - Los datos del formulario del proveedor.
 * @returns {void}
 */
export async function insertProveedor(formData) {
    showLoader(); // Mostrar el loader
    
    try {
        // Enviar la solicitud POST al servidor con los datos del proveedor
        const response = await fetch(`${window.baseURL}/controller/proveedorAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        const data = await response.json();
        
        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al crear el proveedor');
            return; // Salir de la función si hay error
        }

        // Si el proveedor está inactivo, preguntar al usuario si desea actualizarlo
        if (data.inactive) {
            const confirmacion = confirm(data.message);
            if (!confirmacion) {
                mostrarMensaje("Se canceló la creación del proveedor", 'info', 'Creación cancelada');
                return; // Salir de la función si se cancela la actualización
            }

            // Intentar actualizar el proveedor existente
            const proveedor = await obtenerProveedorPorID(parseInt(data.id), true, true);
            formData.set('accion', 'actualizar');
            formData.append('id', proveedor.ID);
            updateProveedor(formData); // Actualizar el proveedor
            return; // Salir de la función
        }

        // Mostrar mensaje de éxito y recargar los proveedores
        mostrarMensaje(data.message, 'success');
        fetchProveedores(window.currentPage, window.pageSize, window.sort);
        resetSearch('proveedor-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el proveedor.<br>${error}`, 'error', 'Error al crear el proveedor');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Actualiza un proveedor enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario del proveedor al servidor para actualizar un proveedor existente.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los proveedores.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * updateProveedor(formData);
 * 
 * @param {Object} formData - Los datos del formulario del proveedor.
 * @returns {void}
 */
export async function updateProveedor(formData) {
    showLoader(); // Mostrar el loader

    try {
        // Enviar la solicitud POST al servidor con los datos del proveedor
        const response = await fetch(`${window.baseURL}/controller/proveedorAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al actualizar el proveedor');
            return; // Salir de la función si hay error
        }

        // Mostrar mensaje de éxito y recargar los proveedores
        mostrarMensaje(data.message, 'success');
        fetchProveedores(window.currentPage, window.pageSize, window.sort);
        resetSearch('proveedor-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al actualizar el proveedor.<br>${error}`, 'error', 'Error al actualizar el proveedor');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Elimina un proveedor enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de enviar la solicitud de eliminación.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los proveedores.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * deleteProveedor(id);
 * 
 * @param {number} id - El ID del proveedor a eliminar.
 * @returns {void}
 */
export async function deleteProveedor(id) {
    // Solicitar confirmación al usuario antes de eliminar el proveedor
    const confirmacion = confirm('¿Estás seguro de que deseas eliminar este proveedor?');
    if (!confirmacion) {
        mostrarMensaje("Se canceló la eliminación del proveedor", 'info', 'Eliminación cancelada');
        return; // Salir de la función si se cancela la eliminación
    }
    showLoader(); // Mostrar el loader

    try {

        // Enviar la solicitud POST al servidor con el id del proveedor a eliminar
        const response = await fetch(`${window.baseURL}/controller/proveedorAction.php`, {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al eliminar el proveedor');
            return; // Salir de la función si hay error
        }

        // Mostrar mensaje de éxito y recargar los proveedores
        mostrarMensaje(data.message, 'success');
        fetchProveedores(window.currentPage, window.pageSize, window.sort);
        resetSearch('proveedor-search-input'); // Limpiar el campo de búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al eliminar el proveedor.<br>${error}`, 'error', 'Error al eliminar el proveedor');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}