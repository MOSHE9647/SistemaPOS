// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { mostrarMensaje } from "../../gui/notification.js";

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
async function obtenerProductoPorCodigoBarras(codigoBarras) {
    const response = await fetch(
        `${window.baseURL}/controller/productoAction.php?accion=codigo&codigo=${codigoBarras}`
    );
    const data = await response.json();
    if (data.success) {
        return data.producto;
    } else {
        throw new Error(data.message);
    }
}

export function agregarProducto(codigoBarras) {
    obtenerProductoPorCodigoBarras(codigoBarras)
        .then(producto => {
            console.log(producto);
        })
        .catch(error => {
            mostrarMensaje(error.message, 'error', 'Error de búsqueda');
        });
}