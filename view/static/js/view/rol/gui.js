// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { mostrarMensaje } from '../../gui/notification.js';
import { checkEmptyTable } from '../../utils.js';
import { updateRol, insertRol } from './crud.js';

// Variables globales
let roles = [];

/**
 * Renderiza la tabla de roles con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada rol en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el rol.
 * 
 * @param {Array} listaRoles - El arreglo de roles a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
export function renderTable(listaRoles) {
    roles = listaRoles;

    const tableBodyID = 'table-roles-body';
    const tableBody = document.getElementById(tableBodyID);

    tableBody.innerHTML = '';

    roles.forEach(rol => {
        const rolAdmin = rol.ID === 1;

        const row = document.createElement('tr');
        row.setAttribute('data-id', rol.ID);
        row.innerHTML = `
            <td data-field="nombre">${rol.Nombre}</td>
            <td data-field="descripcion">${rol.Descripcion}</td>
            <td class="actions">
                <button 
                    class="btn-edit las la-edit ${rolAdmin ? 'disabled' : ''}" 
                    title="${rolAdmin ? 'No se puede editar' : 'Editar rol'}"
                    ${rolAdmin ? 
                        'onclick="alert(\'No se puede editar el rol de administrador.\')"' : 
                        `onclick="gui.editRol(${rol.ID})"`
                    }
                >
                </button>
                <button 
                    class="btn-delete las la-trash ${rolAdmin ? 'disabled' : ''}" 
                    title="${rolAdmin ? 'No se puede eliminar' : 'Eliminar rol'}"
                    ${rolAdmin ? 
                        'onclick="alert(\'No se puede eliminar el rol de administrador.\')"' : 
                        `onclick="deleteRol(${rol.ID})"`
                    }
                >
                </button>
            </td>
        `;

        tableBody.appendChild(row);
    });

    checkEmptyTable(tableBodyID, 'las la-exclamation-circle');
}

/**
 * Abre un modal para editar un rol.
 * 
 * @description Esta función abre un modal con un formulario para editar los datos del rol.
 *              Los campos del formulario se llenan con los datos actuales del rol.
 *              Al confirmar, se validan los datos y se envían para actualizar el rol.
 * 
 * @param {number} rolID - El ID del rol a editar
 * 
 * @example
 * editRol(1);
 * 
 * @returns {void}
 */
export function editRol(rolID) {
    const rol = roles.find(rol => rol.ID == rolID);
    if (!rol) {
        mostrarMensaje('No se encontró el rol seleccionado.', 'error', 'Rol no encontrado');
        return;
    }

    // Crear campos editables para los datos del rol
    let html = `
        <div class="modal-form-container">
            <form id="rol-edit-form">
                <div class="rol-info">
                    <div class="rol-info input-select">
                        <label>Nombre del Rol:</label>
                        <input 
                            type="text" 
                            id="nombre" 
                            placeholder="Ej: Cajero(a), Dependiente, etc..." 
                            value="${rol.Nombre}" required
                        >
                    </div>
                    <div class="rol-info input-select">
                        <label>Descripci&oacute;n:</label>
                        <textarea 
                            id="descripcion" 
                            wrap="soft" 
                            placeholder="Escribe aquí la descripci&oacute;n del rol..."
                        >${rol.Descripcion}</textarea>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Editar rol",
        html: html,
        showCancelButton: true,
        confirmButtonText: "Actualizar",
        cancelButtonText: "Cancelar",
        customClass: {
            popup: 'modal-container',
            header: 'modal-header',
            title: 'modal-title',
            htmlContainer: 'modal-body',
            cancelButton: 'modal-close',
            confirmButton: 'modal-confirm',
            actions: 'modal-actions',
        },
        preConfirm: () => {
            const form = document.getElementById('rol-edit-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }

            // Obtener datos del formulario si es válido
            const formData = new FormData();
            formData.append('accion', 'actualizar');
            formData.append('id', rolID);
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('descripcion', document.getElementById('descripcion').value);

            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            updateRol(formData);
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al actualizar el rol.<br>${error}`, 'error', 'Error al actualizar');
    });
}

/**
 * Abre un modal para crear un nuevo rol.
 * 
 * @description Esta función abre un modal con un formulario para ingresar los datos de un nuevo rol.
 *              Al confirmar, se validan los datos y se envían para crear el nuevo rol.
 * 
 * @example
 * createRol();
 * 
 * @returns {void}
 */
export function createRol() {
    // Crear campos editables para los datos del rol
    let html = `
        <div class="modal-form-container">
            <form id="rol-edit-form">
                <div class="rol-info">
                    <div class="rol-info input-select">
                        <label>Nombre del Rol:</label>
                        <input type="text" placeholder="Ej: Cajero(a), Dependiente, etc..." id="nombre" required>
                    </div>
                    <div class="rol-info input-select">
                        <label>Descripci&oacute;n:</label>
                        <textarea 
                            id="descripcion" 
                            wrap="soft" 
                            placeholder="Escribe aquí la descripci&oacute;n del rol..."
                        ></textarea>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Crear rol",
        html: html,
        showCancelButton: true,
        confirmButtonText: "Crear",
        cancelButtonText: "Cancelar",
        customClass: {
            popup: 'modal-container',
            header: 'modal-header',
            title: 'modal-title',
            htmlContainer: 'modal-body',
            cancelButton: 'modal-close',
            confirmButton: 'modal-confirm',
            actions: 'modal-actions',
        },
        preConfirm: () => {
            const form = document.getElementById('rol-edit-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }

            // Obtener datos del formulario si es válido
            const formData = new FormData();
            formData.append('accion', 'insertar');
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('descripcion', document.getElementById('descripcion').value);

            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            insertRol(formData);
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al crear el nuevo rol.<br>${error}`, 'error', 'Error al crear');
    });
}
