// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { showTableLoader } from "../../gui/loader.js";
import { mostrarMensaje } from "../../gui/notification.js";
import { verificarRespuestaJSON } from "../../utils.js";

// Constantes y variables
const tableBodyID = 'table-telefonos-body';
const loadingMessage = 'Cargando teléfonos...';

/**
 * Verifica la existencia de un teléfono en el sistema.
 *
 * @async
 * @function existeTelefono
 * @param {Object} telefono - El objeto que representa el teléfono a verificar.
 * @param {boolean} [insert=false] - Indica si se está intentando insertar un nuevo teléfono.
 * @param {boolean} [update=false] - Indica si se está intentando actualizar un teléfono existente.
 * @returns {Promise<boolean>} - Retorna una promesa que resuelve a `true` si el teléfono existe, `false` si no existe, 
 *                               o `false` si el usuario decide no reactivar un teléfono inactivo.
 * @throws {Error} - Lanza un error si ocurre algún problema durante la verificación.
 *
 * @description
 * Esta función realiza una solicitud asíncrona al servidor para verificar si un teléfono ya existe en el sistema.
 * Si el teléfono está inactivo, se le pregunta al usuario si desea reactivarlo. En caso de error, se muestra un mensaje
 * de error y se lanza una excepción.
 */
async function existeTelefono(telefono, insert = false, update = false) {
    try {
        const queryParams = new URLSearchParams({
            accion: 'exists',
            telefono: JSON.stringify(telefono),
            insert: insert ? 1 : 0,
            update: update ? 1 : 0
        });

        const response = await fetch(`${window.baseURL}/controller/telefonoAction.php?${queryParams}`);
        if (!response.ok) mostrarMensaje(`Error ${response.status} (${response.statusText})`, 'error', 'Error al crear el producto');
        
        const data = await verificarRespuestaJSON(response);
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al verificar la existencia del teléfono');
            throw new Error(data.message);
        }

        if (data.inactive) {
            const confirm = window.confirm(data.message + '\n\n¿Desea reactivar el teléfono?');
            return confirm ? false : true;
        }

        return data.exists;
    } catch (error) {
        throw new Error(error.message);
    }
}

/**
 * Inserta un nuevo teléfono en la lista de teléfonos.
 * 
 * @description Esta función obtiene los valores de los campos de entrada de una fila de creación y 
 *              añade un nuevo teléfono a la lista de teléfonos.
 * 
 * @example
 * const telefonos = [
 *   { ID: 1, Estado: true, Numero: '1234567890' },
 *   { ID: 2, Estado: true, Numero: '0987654321' }
 * ];
 * 
 * insertTelefono(telefonos).then(() => {
 *   console.log(telefonos);
 *   // [
 *   //   { ID: -1, Estado: true, Numero: '1122334455' },
 *   //   { ID: 1, Estado: true, Numero: '1234567890' },
 *   //   { ID: 2, Estado: true, Numero: '0987654321' }
 *   // ]
 * });
 * 
 * @param {Array<Object>} telefonos - La lista de teléfonos a la que se añadirá el nuevo teléfono.
 * @returns {void}
 */
export async function insertTelefono(telefonos, row) {
    try {
        // Obtener la fila de creación
        if (!row) {
            mostrarMensaje('No se encontró la fila de creación', 'error', 'Error al añadir el teléfono');
            return false;
        }

        // Obtener los valores de los campos de entrada
        const inputs = row.querySelectorAll('input, select');
        const maxId = telefonos.reduce((max, telefono) => Math.max(max, telefono.ID), 0);
        const data = { ID: maxId + 1, Estado: true };

        // Mostrar el mensaje de carga en la tabla
        showTableLoader(tableBodyID, loadingMessage);

        for (const input of inputs) {
            const fieldName = input.closest('td').dataset.field; // Obtener el nombre del campo
            let value = input.value;

            // Validar que el número de teléfono sea válido
            if (fieldName === 'Numero') {
                if (value === '') {
                    mostrarMensaje(`El campo '${fieldName}' no puede estar vacío`, 'error', 'Error al añadir el teléfono');
                    return false;
                }
            }

            // Asignar el valor al campo correspondiente
            data[fieldName] = value;
        }

        // Verificar si el teléfono ya existe en la base de datos
        if (await existeTelefono(data, true)) {
            mostrarMensaje('El teléfono ya existe en la base de datos', 'error', 'Error al añadir el teléfono');
            return false;
        }

        // Añadir el nuevo teléfono a la lista de teléfonos
        telefonos.unshift(data);
        return true;
    } catch (error) {
        mostrarMensaje(`${error}`, 'error', 'Error al añadir el teléfono');
        return false;
    }
}

/**
 * Actualiza un teléfono en la lista de teléfonos.
 * 
 * @description Esta función obtiene los valores de los campos de entrada de una fila de edición 
 *              y actualiza el teléfono correspondiente en la lista de teléfonos.
 * 
 * @example
 * const telefonos = [
 *   { ID: 1, Estado: true, Numero: '1234567890' },
 *   { ID: 2, Estado: true, Numero: '0987654321' }
 * ];
 * 
 * updateTelefono(telefonos, 1);
 * 
 * @param {Array<Object>} telefonos - La lista de teléfonos en la que se actualizará el teléfono.
 * @param {number} id - El ID del teléfono a actualizar.
 * @returns {void}
 */
export async function updateTelefono(telefonos, row) {
    try {
        if (!row) {
            mostrarMensaje('No se encontró la fila del teléfono a actualizar', 'error', 'Error al actualizar el teléfono');
            return false;
        }
    
        const id = row.dataset.id;
        const inputs = row.querySelectorAll('input, select');
        const data = { ID: parseInt(id), Estado: true };

        // Mostrar el mensaje de carga en la tabla
        showTableLoader(tableBodyID, loadingMessage);
    
        for (const input of inputs) {
            const fieldName = input.closest('td').dataset.field; // Obtener el nombre del campo
            let value = input.value;
    
            // Validar que el número de teléfono sea válido
            if (fieldName === 'Numero') {
                if (value === '') {
                    mostrarMensaje(`El campo '${fieldName}' no puede estar vacío`, 'error', 'Error al añadir el teléfono');
                    return false;
                }
            }
    
            // Asignar el valor al campo correspondiente
            data[fieldName] = value;
        }
    
        // Verificar si el teléfono ya existe en la base de datos
        if (await existeTelefono(data, false, true)) {
            mostrarMensaje('El teléfono ya existe en la base de datos', 'error', 'Error al actualizar el teléfono');
            return false;
        }
    
        const index = telefonos.findIndex(telefono => telefono.ID === parseInt(id));
        if (index === -1) {
            mostrarMensaje('No se encontró el teléfono a actualizar', 'error', 'Error al actualizar el teléfono');
            return false;
        }
    
        telefonos[index] = data;
        return true;
    } catch (error) {
        mostrarMensaje(`${error}`, 'error', 'Error al actualizar el teléfono');
        return false;
    }
}

/**
 * Elimina un teléfono de la lista de teléfonos.
 * 
 * @description Esta función solicita confirmación al usuario antes de eliminar el teléfono de la lista.
 *              Si el teléfono no se encuentra, muestra un mensaje de error.
 * 
 * @example
 * const telefonos = [
 *   { ID: 1, Estado: true, Numero: '1234567890' },
 *   { ID: 2, Estado: true, Numero: '0987654321' }
 * ];
 * 
 * deleteTelefono(telefonos, 1);
 * 
 * @param {Array<Object>} telefonos - La lista de teléfonos de la que se eliminará el teléfono.
 * @param {number} id - El ID del teléfono a eliminar.
 * @returns {void}
 */
export function deleteTelefono(telefonos, id) {
    if (!confirm('¿Está seguro de que desea eliminar este teléfono?')) {
        mostrarMensaje('No se eliminó el teléfono', 'info', 'Eliminación cancelada');
        return;
    }

    // Mostrar el mensaje de carga en la tabla
    showTableLoader(tableBodyID, loadingMessage);

    const index = telefonos.findIndex(telefono => telefono.ID === parseInt(id));
    if (index === -1) {
        mostrarMensaje('No se encontró el teléfono a eliminar', 'error', 'Error al eliminar el teléfono');
        return;
    }

    telefonos.splice(index, 1);
}