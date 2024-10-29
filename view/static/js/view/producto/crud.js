// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { hideLoader, showLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { fetchProductos } from "./pagination.js";
import { resetSearch } from "../../utils.js";

/**
 * Obtiene un producto por su ID.
 *
 * @async
 * @function obtenerProductoPorID
 * @param {number} id - El ID del producto a obtener.
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención del producto.
 * @param {boolean} [deleted=false] - Si se deben incluir productos eliminados en la obtención.
 * @returns {Promise<Object>} Los datos del producto si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o el producto no se encuentra.
 */
async function obtenerProductoPorID(id, filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const response = await fetch(
        `${window.baseURL}/controller/productoAction.php?accion=id&id=${id}&filter=${filterNum}&deleted=${deletedNum}`
    );
    const data = await response.json();
    if (data.success) {
        return data.producto;
    } else {
        throw new Error(data.message);
    }
}

/**
 * Obtiene una lista de productos desde la BD.
 *
 * @async
 * @function obtenerListaProductos
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención de la lista.
 * @param {boolean} [deleted=false] - Si se deben incluir productos eliminados en la obtención.
 * @returns {Promise<Object>} Los datos de la lista si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o la lista no se encuentra.
 */
export function obtenerListaProductos(filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const request = new XMLHttpRequest();
    request.open('GET', `${window.baseURL}/controller/productoAction.php?accion=all&filter=${filterNum}&deleted=${deletedNum}`, false);
    request.send(null);

    if (request.status === 200) {
        const data = JSON.parse(request.responseText);
        if (data.success) {
            return data.productos;
        } else {
            throw new Error(data.message);
        }
    } else {
        throw new Error('Error en la solicitud');
    }
}

/**
 * Crea un nuevo producto enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario del producto al servidor para crear un nuevo producto.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los productos.
 *              Si la solicitud falla, muestra un mensaje de error. Si el producto ya existe pero está inactivo,
 *              pregunta al usuario si desea actualizarlo.
 * 
 * @example
 * insertProducto(formData);
 * 
 * @param {Object} formData - Los datos del formulario del producto.
 * @returns {void}
 */
export async function insertProducto(formData) {
    showLoader(); // Mostrar el loader
    
    try {
        // Enviar la solicitud POST al servidor con los datos del producto
        const response = await fetch(`${window.baseURL}/controller/productoAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();
        
        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al crear el producto');
            return; // Salir de la función si hay error
        }

        // Si el producto está inactivo, preguntar al usuario si desea actualizarlo
        if (data.inactive) {
            const confirmacion = confirm(data.message);
            if (!confirmacion) {
                mostrarMensaje("Se canceló la creación del producto", 'info', 'Creación cancelada');
                return; // Salir de la función si se cancela la actualización
            }

            // Intentar actualizar el producto existente
            const producto = await obtenerProductoPorID(parseInt(data.id), true, true);
            formData.set('accion', 'actualizar');
            formData.append('id', producto.ID);
            formData.append('codigobarras', producto.CodigoBarras.ID);
            updateProducto(formData); // Actualizar el producto
            return; // Salir de la función
        }

        // Mostrar mensaje de éxito y recargar los productos
        mostrarMensaje(data.message, 'success');
        fetchProductos(window.currentPage, window.pageSize, window.sort);
        resetSearch('producto-search-input'); // Limpiar la búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al crear el producto.<br>${error}`, 'error', 'Error al crear el producto');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Actualiza un producto enviando una solicitud POST al servidor.
 * 
 * @description Esta función envía los datos del formulario del producto al servidor para actualizar un producto existente.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los productos.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * updateProducto(formData);
 * 
 * @param {Object} formData - Los datos del formulario del producto.
 * @returns {void}
 */
export async function updateProducto(formData) {
    showLoader(); // Mostrar el loader

    try {
        // Enviar la solicitud POST al servidor con los datos del producto
        const response = await fetch(`${window.baseURL}/controller/productoAction.php`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al actualizar el producto');
            return; // Salir de la función si hay error
        }

        // Mostrar mensaje de éxito y recargar los productos
        mostrarMensaje(data.message, 'success');
        fetchProductos(window.currentPage, window.pageSize, window.sort);
        resetSearch('producto-search-input'); // Limpiar la búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al actualizar el producto.<br>${error}`, 'error', 'Error al actualizar el producto');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}

/**
 * Elimina un producto enviando una solicitud POST al servidor.
 * 
 * @description Esta función solicita confirmación al usuario antes de enviar la solicitud de eliminación.
 *              Si la solicitud es exitosa, muestra un mensaje de éxito y recarga los datos de los productos.
 *              Si la solicitud falla, muestra un mensaje de error.
 * 
 * @example
 * deleteProducto(id);
 * 
 * @param {number} id - El ID del producto a eliminar.
 * @returns {void}
 */
export async function deleteProducto(id) {
    // Solicitar confirmación al usuario antes de eliminar el producto
    const confirmacion = confirm('¿Estás seguro de que deseas eliminar este producto?');
    if (!confirmacion) {
        mostrarMensaje("Se canceló la eliminación del producto", 'info', 'Eliminación cancelada');
        return; // Salir de la función si se cancela la eliminación
    }
    showLoader(); // Mostrar el loader

    try {

        // Enviar la solicitud POST al servidor con el id del producto a eliminar
        const response = await fetch(`${window.baseURL}/controller/productoAction.php`, {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        const data = await response.json();

        // Verificar si hubo un error en la solicitud
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al eliminar el producto');
            return; // Salir de la función si hay error
        }

        // Mostrar mensaje de éxito y recargar los productos
        mostrarMensaje(data.message, 'success');
        fetchProductos(window.currentPage, window.pageSize, window.sort);
        resetSearch('producto-search-input'); // Limpiar la búsqueda
    } catch (error) {
        // Mostrar mensaje de error detallado
        mostrarMensaje(`Ocurrió un error al eliminar el producto.<br>${error}`, 'error', 'Error al eliminar el producto');
    } finally {
        hideLoader(); // Ocultar el loader
    }
}