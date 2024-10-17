import { showLoader, hideLoader } from '../../gui/loader.js';
import { mostrarMensaje } from '../../gui/notification.js';
import { checkEmptyTable } from '../../utils.js';
import { initializeSelects } from './selects.js';
import { updateUsuario, insertUsuario } from './crud.js';

// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

// Variables globales
let usuarios = [];

/**
 * Renderiza la tabla de usuarios con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada usuario en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el usuario.
 * 
 * @param {Array} listaUsuarios - El arreglo de usuarios a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
export function renderTable(listaUsuarios) {
    showLoader();
    usuarios = listaUsuarios;

    // Obtener el cuerpo de la tabla
    let tableBodyID = 'table-usuarios-body';
    let tableBody = document.getElementById(tableBodyID);
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada usuario en el arreglo
    usuarios.forEach(usuario => {
        // Obtener el rol del usuario actual
        const RolID = usuario.RolUsuario.ID || -1;
        const RolNombre = usuario.RolUsuario.Nombre || 'Sin rol asignado';
        const disabled = correoUsuario == usuario.Email; // Deshabilitar opciones si es el usuario actual

        // Crear una fila para el usuario
        let row = `
            <tr data-id="${usuario.ID}">
            <td data-field="correo">${usuario.Email}</td>
            <td data-field="nombre">${usuario.Nombre}</td>
            <td data-field="apellido1">${usuario.Apellido1}</td>
            <td data-field="apellido2">${usuario.Apellido2}</td>
            <td data-field="rol" data-id="${RolID}">${RolNombre}</td>
            <td data-field="creacion" data-iso="${usuario.CreacionISO}">${usuario.Creacion}</td>
            <td data-field="modificacion" data-iso="${usuario.ModificacionISO}">${usuario.Modificacion}</td>
            <td class="actions">
                <button class="btn-edit las la-edit" onclick="gui.editUsuario(${usuario.ID})"></button>
                <button 
                    class="btn-delete ${disabled ? 'disabled' : ''} las la-trash" 
                    onclick="${disabled ? 'alert(\'No se puede eliminar al usuario actual.\')' : 'deleteUsuario(' + usuario.ID + ')'}">
                </button>
            </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    checkEmptyTable(tableBodyID, 'las la-user-times');
    hideLoader();
}

/**
 * Abre un modal para editar un usuario.
 * 
 * @description Esta función abre un modal con un formulario para editar los datos del usuario.
 *              Los campos del formulario se llenan con los datos actuales del usuario.
 *              Al confirmar, se validan los datos y se envían para actualizar el usuario.
 * 
 * @param {number} usuarioID - El ID del usuario a editar
 * 
 * @example
 * editUsuario(1);
 * 
 * @returns {void}
 */
export function editUsuario(usuarioID) {
    const usuario = usuarios.find(usuario => usuario.ID == usuarioID);
    if (!usuario) {
        mostrarMensaje('No se encontró el usuario seleccionado.', 'error', 'Error al editar');
        return; // Salir de la función si no se encuentra el usuario
    }

    // Crear campos editables para los datos del usuario
    let html = `
        <div class="modal-form-container">
            <form id="usuario-edit-form">
                <div class="usuario-info name">
                    <div class="usuario-info input-select name">
                        <label>Nombre del Usuario:</label>
                        <input type="text" id="nombre" value="${usuario.Nombre}" required>
                    </div>
                    <div class="usuario-info input-select group">
                        <div class="usuario-info input-select">
                            <label>Primer Apellido:</label>
                            <input type="text" id="apellido1" value="${usuario.Apellido1}" required>
                        </div>
                        <div class="usuario-info input-select">
                            <label>Segundo Apellido:</label>
                            <input type="text" id="apellido2" value="${usuario.Apellido2}" required>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="usuario-info email-rol">
                    <div class="usuario-info input-select group">
                        <div class="usuario-info input-select">
                            <label>Rol del Usuario:</label>
                            <select data-field="rol" id="rol-select" disabled>
                                <option value="${usuario.RolUsuario.ID}" selected>${usuario.RolUsuario.Nombre}</option>
                            </select>
                        </div>
                        <div class="usuario-info input-select email">
                            <label>Correo Electr&oacute;nico:</label>
                            <input type="email" id="email" value="${usuario.Email}" autocomplete="username" required>
                        </div>
                    </div>
                </div>
                <div class="usuario-info password">
                    <div class="usuario-info input-select group">
                        <div class="usuario-info input-select">
                            <label>Nueva Contrase&ntilde;a:</label>
                            <input type="password" id="password" autocomplete="new-password" minlength="8" maxlength="16">
                        </div>
                        <div class="usuario-info input-select">
                            <label>Confirmar Contrase&ntilde;a:</label>
                            <input type="password" id="confirm-password" autocomplete="new-password" minlength="8" maxlength="16">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Editar usuario",
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
            const form = document.getElementById('usuario-edit-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }

            // Obtener la información de la nueva contraseña introducida por el usuario
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            // Validar que las contraseñas coincidan
            if (password && password !== confirmPassword) {
                mostrarMensaje('Las contraseñas no coinciden.', 'error', 'Error al actualizar');
                return false; // Previene que Swal cierre si las contraseñas no coinciden
            }

            // Obtener datos del formulario si es válido
            const formData = new FormData();
            formData.append('accion'    , 'actualizar');
            formData.append('id'        , usuario.ID);
            formData.append('nombre'    , document.getElementById('nombre').value);
            formData.append('apellido1' , document.getElementById('apellido1').value);
            formData.append('apellido2' , document.getElementById('apellido2').value);
            formData.append('correo'    , document.getElementById('email').value);
            formData.append('rol'       , document.getElementById('rol-select').value);
            if (password) formData.append('password', password);

            return formData; // Pasar los datos a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            updateUsuario(formData);
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al crear el nuevo usuario.<br>${error}`, 'error', 'Error al crear');
    });
    
    // Llenar los selects después de que la fila esté preparada
    initializeSelects();
}

/**
 * Abre un modal para crear un nuevo usuario.
 * 
 * @description Esta función abre un modal con un formulario para ingresar los datos de un nuevo usuario.
 *              Al confirmar, se validan los datos y se envían para crear el nuevo usuario.
 * 
 * @example
 * createUsuario();
 * 
 * @returns {void}
 */
export function createUsuario() {
    // Crear campos editables para los datos del usuario
    let html = `
        <div class="modal-form-container">
            <form id="usuario-edit-form">
                <div class="usuario-info name">
                    <div class="usuario-info input-select name">
                        <label>Nombre del Usuario:</label>
                        <input type="text" id="nombre" required>
                    </div>
                    <div class="usuario-info input-select group">
                        <div class="usuario-info input-select">
                            <label>Primer Apellido:</label>
                            <input type="text" id="apellido1" required>
                        </div>
                        <div class="usuario-info input-select">
                            <label>Segundo Apellido:</label>
                            <input type="text" id="apellido2" required>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="usuario-info email-rol">
                    <div class="usuario-info input-select group">
                        <div class="usuario-info input-select">
                            <label>Rol del Usuario:</label>
                            <select data-field="rol" id="rol-select" required>
                                <option>--Seleccionar--</option>
                            </select>
                        </div>
                        <div class="usuario-info input-select email">
                            <label>Correo Electr&oacute;nico:</label>
                            <input type="email" id="email" autocomplete="username" required>
                        </div>
                    </div>
                </div>
                <div class="usuario-info password">
                    <div class="usuario-info input-select group">
                        <div class="usuario-info input-select">
                            <label>Nueva Contrase&ntilde;a:</label>
                            <input type="password" id="password" autocomplete="new-password" minlength="8" maxlength="16" required>
                        </div>
                        <div class="usuario-info input-select">
                            <label>Confirmar Contrase&ntilde;a:</label>
                            <input type="password" id="confirm-password" autocomplete="new-password" minlength="8" maxlength="16" required>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Crear usuario",
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
            const form = document.getElementById('usuario-edit-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }

            // Obtener la información de la nueva contraseña introducida por el usuario
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            // Validar que las contraseñas coincidan
            if (password !== confirmPassword) {
                mostrarMensaje('Las contraseñas no coinciden.', 'error', 'Error al actualizar');
                return false; // Previene que Swal cierre si las contraseñas no coinciden
            }

            // Obtener datos del formulario si es válido
            const formData = new FormData();
            formData.append('accion'    , 'insertar');
            formData.append('nombre'    , document.getElementById('nombre').value);
            formData.append('apellido1' , document.getElementById('apellido1').value);
            formData.append('apellido2' , document.getElementById('apellido2').value);
            formData.append('correo'    , document.getElementById('email').value);
            formData.append('rol'       , document.getElementById('rol-select').value);
            formData.append('password'  , password);

            return formData; // Pasar los datos a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            insertUsuario(formData);
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al crear el nuevo usuario.<br>${error}`, 'error', 'Error al crear');
    });
    
    // Llenar los selects después de que la fila esté preparada
    initializeSelects();
}
