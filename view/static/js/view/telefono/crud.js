// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { mostrarMensaje } from "../../gui/notification.js";

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
export function insertTelefono(telefonos) {
    const row = document.querySelector('.creating-row');
    const inputs = row.querySelectorAll('input, select');
    const maxId = telefonos.reduce((max, telefono) => Math.max(max, telefono.ID), 0);
    const data = { ID: maxId + 1, Estado: true };

    for (const input of inputs) {
        const fieldName = input.closest('td').dataset.field;
        data[fieldName] = input.value;
    }

    telefonos.unshift(data);
    mostrarMensaje('Teléfono añadido exitosamente', 'success', 'Inserción exitosa');
    return true;
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
export function updateTelefono(telefonos, id) {
    const row = document.querySelector(`tr[data-id='${id}']`);
    if (!row) {
        mostrarMensaje('No se encontró la fila del teléfono a actualizar', 'error', 'Error al actualizar el teléfono');
        return false;
    }

    const inputs = row.querySelectorAll('input, select');
    const data = { ID: parseInt(id), Estado: true };

    for (const input of inputs) {
        const fieldName = input.closest('td').dataset.field;
        data[fieldName] = input.value;
    }

    const index = telefonos.findIndex(telefono => telefono.ID === parseInt(id));
    if (index === -1) {
        mostrarMensaje('No se encontró el teléfono a actualizar', 'error', 'Error al actualizar el teléfono');
        return false;
    }

    telefonos[index] = data;
    mostrarMensaje('Teléfono actualizado exitosamente', 'success', 'Actualización exitosa');
    return true;
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
    if (!confirm('¿Está seguro de que desea eliminar este teléfono?')) return;

    const index = telefonos.findIndex(telefono => telefono.ID === parseInt(id));
    if (index === -1) {
        mostrarMensaje('No se encontró el teléfono a eliminar', 'error', 'Error al eliminar el teléfono');
        return;
    }

    telefonos.splice(index, 1);
    mostrarMensaje('Teléfono eliminado exitosamente', 'success', 'Eliminación exitosa');
}