// ************************************************************************************************ //
// ************* Métodos para insertar, actualizar o eliminar un registro de la tabla ************* //
// ************************************************************************************************ //

import { mostrarMensaje } from "../../gui/notification.js";

async function existeDireccion(direccion, insert = false, update = false) {
    try {
        const queryParams = new URLSearchParams({
            accion: 'exists',
            direccion: JSON.stringify(direccion),
            insert: insert ? 1 : 0,
            update: update ? 1 : 0
        });

        const response = await fetch(`${window.baseURL}/controller/direccionAction.php?${queryParams}`);
        if (!response.ok) throw new Error(`Error ${response.status} (${response.statusText})`);
        
        const data = await response.json();
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al verificar la existencia de la dirección');
            throw new Error(data.message);
        }

        return data.exists;
    } catch (error) {
        throw new Error(error.message);
    }
}

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
 *   //   { ID: 3, Estado: true, Calle: 'NUEVA CALLE', Distancia: '3.00' },
 *   //   { ID: 1, Estado: true, Calle: 'PRINCIPAL', Distancia: '10.00' },
 *   //   { ID: 2, Estado: true, Calle: 'SECUNDARIA', Distancia: '5.50' }
 *   // ]
 * });
 * 
 * @param {Array<Object>} direcciones - La lista de direcciones a la que se añadirá la nueva dirección.
 * @returns {Promise<boolean>} - Retorna una promesa que resuelve a true si la inserción fue exitosa, de lo contrario false.
 */
export async function insertDireccion(direcciones) {
    try {
        // Obtener la fila de creación
        const row = document.querySelector('.creating-row');
        if (!row) {
            mostrarMensaje('No se encontró la fila de creación', 'error', 'Error en la inserción');
            return false;
        }

        // Obtener los valores de los campos de entrada
        const inputs = row.querySelectorAll('input, select');
        const maxId = direcciones.reduce((max, direccion) => Math.max(max, direccion.ID), 0);
        const data = { ID: maxId + 1, Estado: true };

        // Validar los valores de los campos de entrada
        for (const input of inputs) {
            const fieldName = input.closest('td').dataset.field;
            let value = input.value.trim().replace(/\s+/g, ' ');

            // Validar si el campo 'Distancia' es un número mayor a 0
            if (fieldName === 'Distancia') {
                if (value === '' || parseFloat(value) === 0) {
                    mostrarMensaje(`El campo '${fieldName}' no puede estar vacío ni ser 0`, 'error', 'Error en la inserción');
                    return false;
                }
                value = parseFloat(value).toFixed(2);
            } else {
                value = value.toUpperCase();
            }

            // Asignar el valor al campo correspondiente
            data[fieldName] = value;
        }

        // Verificar si la dirección ya existe en base de datos
        if (await existeDireccion(data, true)) {
            mostrarMensaje('La dirección ya existe en la base de datos', 'error', 'Error en la inserción');
            return false;
        }

        // Añadir la nueva dirección a la lista de direcciones
        direcciones.unshift(data);
        mostrarMensaje('Dirección añadida exitosamente', 'success', 'Inserción exitosa');
        return true;
    } catch (error) {
        mostrarMensaje(`${error}`, 'error', 'Error en la inserción');
        return false;
    }
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
 * updateDireccion(direcciones, 1).then((success) => {
 *   if (success) {
 *     console.log('Dirección actualizada:', direcciones);
 *   }
 * });
 * 
 * @param {Array<Object>} direcciones - La lista de direcciones en la que se actualizará la dirección.
 * @param {number} id - El ID de la dirección a actualizar.
 * @returns {Promise<boolean>} - Retorna una promesa que resuelve a true si la actualización fue exitosa, de lo contrario false.
 */
export async function updateDireccion(direcciones, row) {
    try {
        // const row = document.querySelector(`tr[data-id='${id}']`);
        if (!row) {
            mostrarMensaje('No se encontró la fila de la dirección a actualizar', 'error', 'Error al actualizar la dirección');
            return false;
        }

        const id = row.dataset.id;
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

        // Verificar si la dirección ya existe en base de datos
        if (await existeDireccion(data, false, true)) {
            mostrarMensaje('Ya existe una dirección con los mismos datos en la base de datos', 'error', 'Error en la actualización');
            return false;
        }

        const index = direcciones.findIndex(direccion => direccion.ID === parseInt(id));
        if (index === -1) {
            mostrarMensaje('No se encontró la dirección a actualizar', 'error', 'Error al actualizar la dirección');
            return false;
        }

        direcciones[index] = data;
        mostrarMensaje('Dirección actualizada exitosamente', 'success', 'Actualización exitosa');
        return true;
    } catch (error) {
        mostrarMensaje(`${error}`, 'error', 'Error en la actualización');
        return false;
    }
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