// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { mostrarMensaje } from "../../gui/notification.js";

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
    const data = await response.json();
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