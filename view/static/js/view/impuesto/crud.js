// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

/**
 * Obtiene un impuesto por su ID.
 *
 * @async
 * @function obtenerImpuestoPorID
 * @param {number} id - El ID del impuesto a obtener.
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención del impuesto.
 * @param {boolean} [deleted=false] - Si se deben incluir impuestos eliminados en la obtención.
 * @returns {Promise<Object>} Los datos del impuesto si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o el impuesto no se encuentra.
 */
async function obtenerImpuestoPorID(id, filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const response = await fetch(
        `${window.baseURL}/controller/impuestoAction.php?accion=id&id=${id}&filter=${filterNum}&deleted=${deletedNum}`
    );
    const data = await response.json();
    if (data.success) {
        return data.impuesto;
    } else {
        throw new Error(data.message);
    }
}

/**
 * Obtiene una lista de impuestos desde la BD.
 *
 * @async
 * @function obtenerListaImpuestos
 * @param {boolean} [filter=true] - Si se deben aplicar filtros a la obtención de la lista.
 * @param {boolean} [deleted=false] - Si se deben incluir impuestos eliminados en la obtención.
 * @returns {Promise<Object>} Los datos de la lista si la solicitud es exitosa.
 * @throws {Error} Si la solicitud falla o la lista no se encuentra.
 */
export function obtenerListaImpuestos(filter = true, deleted = false) {
    const filterNum = filter ? 1 : 0, deletedNum = deleted ? 1 : 0;
    const request = new XMLHttpRequest();
    request.open('GET', `${window.baseURL}/controller/impuestoAction.php?accion=all&filter=${filterNum}&deleted=${deletedNum}`, false);
    request.send(null);

    if (request.status === 200) {
        const data = JSON.parse(request.responseText);
        if (data.success) {
            return data.impuestos;
        } else {
            throw new Error(data.message);
        }
    } else {
        throw new Error('Error en la solicitud');
    }
}