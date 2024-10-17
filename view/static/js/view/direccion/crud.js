// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { mostrarMensaje } from "../../gui/notification.js";

/**
 * Inserta una nueva dirección en la lista de direcciones.
 * 
 * @description Esta función obtiene los valores de los campos de entrada de una fila de creación y 
 *              añade una nueva dirección a la lista de direcciones.
 * 
 * @example
 * const direcciones = [
 *   { ID: 1, Estado: true, Calle: 'PRINCIPAL', Distancia: '10.00' },
 *   { ID: 2, Estado: true, Calle: 'SECUNDARIA', Distancia: '5.50' }
 * ];
 * 
 * insertDireccion(direcciones).then(() => {
 *   console.log(direcciones);
 *   // [
 *   //   { ID: -1, Estado: true, Calle: 'NUEVA CALLE', Distancia: '3.00' },
 *   //   { ID: 1, Estado: true, Calle: 'PRINCIPAL', Distancia: '10.00' },
 *   //   { ID: 2, Estado: true, Calle: 'SECUNDARIA', Distancia: '5.50' }
 *   // ]
 * });
 * 
 * @param {Array<Object>} direcciones - La lista de direcciones a la que se añadirá la nueva dirección.
 * @returns {void}
 */
export function insertDireccion(direcciones) {
    const row = document.querySelector('.creating-row');
    const inputs = row.querySelectorAll('input, select');
    const maxId = direcciones.reduce((max, direccion) => Math.max(max, direccion.ID), 0);
    const data = { ID: maxId + 1, Estado: true };

    for (const input of inputs) {
        const fieldName = input.closest('td').dataset.field;
        let value = input.value.trim().replace(/\s+/g, ' ');

        if (fieldName === 'Distancia') {
            if (value === '' || parseFloat(value) === 0) {
                mostrarMensaje(`El campo '${fieldName}' no puede estar vacío ni ser 0`, 'error', 'Error en la inserción');
                return false;
            }
            value = parseFloat(value).toFixed(2);
        } else {
            value = value.toUpperCase();
        }

        data[fieldName] = value;
    }

    direcciones.unshift(data);
    mostrarMensaje('Dirección añadida exitosamente', 'success', 'Inserción exitosa');
    return true;
}

/**
 * Actualiza una dirección en la lista de direcciones.
 * 
 * @description Esta función obtiene los valores de los campos de entrada de una fila de edición 
 *              y actualiza la dirección correspondiente en la lista de direcciones.
 * 
 * @example
 * const direcciones = [
 *   { ID: 1, Estado: true, Calle: 'PRINCIPAL', Distancia: '10.00' },
 *   { ID: 2, Estado: true, Calle: 'SECUNDARIA', Distancia: '5.50' }
 * ];
 * 
 * updateDireccion(direcciones, 1);
 * 
 * @param {Array<Object>} direcciones - La lista de direcciones en la que se actualizará la dirección.
 * @param {number} id - El ID de la dirección a actualizar.
 * @returns {void}
 */
export function updateDireccion(direcciones, id) {
    const row = document.querySelector(`tr[data-id='${id}']`);
    if (!row) {
        mostrarMensaje('No se encontró la fila de la dirección a actualizar', 'error', 'Error al actualizar la dirección');
        return false;
    }

    const inputs = row.querySelectorAll('input, select');
    const data = { ID: parseInt(id), Estado: true };

    for (const input of inputs) {
        const fieldName = input.closest('td').dataset.field;
        let value = input.value.trim().replace(/\s+/g, ' ');

        if (fieldName === 'Distancia') {
            if (value === '' || parseFloat(value) === 0) {
                mostrarMensaje(`El campo '${fieldName}' no puede estar vacío ni ser 0`, 'error', 'Error en la actualización');
                return false;
            }
            value = parseFloat(value).toFixed(2);
        } else {
            value = value.toUpperCase();
        }

        data[fieldName] = value;
    }

    const index = direcciones.findIndex(direccion => direccion.ID === parseInt(id));
    if (index === -1) {
        mostrarMensaje('No se encontró la dirección a actualizar', 'error', 'Error al actualizar la dirección');
        return false;
    }

    direcciones[index] = data;
    mostrarMensaje('Dirección actualizada exitosamente', 'success', 'Actualización exitosa');
    return true;
}

/**
 * Elimina una dirección de la lista de direcciones.
 * 
 * @description Esta función solicita confirmación al usuario antes de eliminar la dirección de la lista.
 *              Si la dirección no se encuentra, muestra un mensaje de error.
 * 
 * @example
 * const direcciones = [
 *   { ID: 1, Estado: true, Calle: 'PRINCIPAL', Distancia: '10.00' },
 *   { ID: 2, Estado: true, Calle: 'SECUNDARIA', Distancia: '5.50' }
 * ];
 * 
 * deleteDireccion(direcciones, 1);
 * 
 * @param {Array<Object>} direcciones - La lista de direcciones de la que se eliminará la dirección.
 * @param {number} id - El ID de la dirección a eliminar.
 * @returns {void}
 */
export function deleteDireccion(direcciones, id) {
    if (!confirm('¿Está seguro de que desea eliminar esta dirección?')) return;

    const index = direcciones.findIndex(direccion => direccion.ID === parseInt(id));
    if (index === -1) {
        mostrarMensaje('No se encontró la dirección a eliminar', 'error', 'Error al eliminar la dirección');
        return;
    }

    direcciones.splice(index, 1);
    mostrarMensaje('Dirección eliminada exitosamente', 'success', 'Eliminación exitosa');
}