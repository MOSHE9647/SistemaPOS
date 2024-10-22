// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { showLoader, hideLoader } from '../../gui/loader.js';
import { mostrarMensaje } from '../../gui/notification.js';
import { checkEmptyTable, manejarInputNumeroTelefono } from '../../utils.js';
import { initializeSelects } from '../telefono/selects.js';
import { updateCliente, insertCliente } from './crud.js';

// Variables globales
let clientes = [];

/**
 * Renderiza la tabla de clientes con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada cliente en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el cliente.
 * 
 * @param {Array} listaClientes - El arreglo de clientes a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
export function renderTable(listaClientes) {
    showLoader();
    clientes = listaClientes;

    // Obtener el cuerpo de la tabla
    let tableBodyID = 'table-clientes-body';
    let tableBody = document.getElementById(tableBodyID);
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada cliente en el arreglo
    clientes.forEach(cliente => {
        // Obtener la información del teléfono y del cliente
        const codigoPais = cliente.Telefono.CodigoPais;
        const numero = cliente.Telefono.Numero;
        const extension = cliente.Telefono.Extension ? `ext. ${cliente.Telefono.Extension}` : '';
        const telefonoCompleto = `${codigoPais} ${numero} ${extension}`;
        const tieneUsuario = cliente.Usuario ? true : false;

        // Crear una fila para el cliente
        let row = `
            <tr data-id="${cliente.ID}">
            ${tieneUsuario ? `
                <td data-field="usuarioemail">${cliente.Usuario.Email}</td>
            ` : ''}
            <td data-field="nombre">${cliente.Nombre}</td>
            <td data-field="alias">${cliente.Alias}</td>
            <td data-field="telefono">${telefonoCompleto}</td>
            <td data-field="telefonotipo">${cliente.Telefono.Tipo}</td>
            ${isAdmin ? `
                <td data-field="creacion" data-iso="${cliente.CreacionISO}">${cliente.Creacion}</td>
                <td data-field="modificacion" data-iso="${cliente.ModificacionISO}">${cliente.Modificacion}</td>
            ` : ''}
            <td class="actions">
                <button class="btn-edit las la-edit" onclick="gui.editCliente(${cliente.ID})"></button>
                <button class="btn-delete las la-trash" onclick="deleteCliente(${cliente.ID})"></button>
            </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    // Verificar si la tabla está vacía
    checkEmptyTable(tableBodyID, 'las la-user-times');
    hideLoader();
}

/**
 * Abre un modal para editar un cliente.
 * 
 * @description Esta función abre un modal con un formulario para editar los datos del cliente.
 *              Los campos del formulario se llenan con los datos actuales del cliente.
 *              Al confirmar, se validan los datos y se envían para actualizar el cliente.
 * 
 * @param {number} clienteID - El ID del cliente a editar
 * 
 * @example
 * editCliente(1);
 * 
 * @returns {void}
 */
export function editCliente(clienteID) {
    const cliente = clientes.find(cliente => cliente.ID == clienteID);
    if (!cliente) {
        mostrarMensaje('No se encontró el cliente solicitado.', 'error', 'Cliente no encontrado');
        return;
    }

    // Crear campos editables para los datos del cliente
    let html = `
        <div class="modal-form-container">
            <form id="cliente-edit-form">
                <div class="cliente-info">
                    <label>Nombre del Cliente:</label>
                    <input type="text" id="nombre" value="${cliente.Nombre}">
                </div>
                <div class="cliente-info">
                    <label>Alias:</label>
                    <input type="text" id="alias" value="${cliente.Alias}">
                </div>
                <hr>
                <div class="telefono-cliente-info">
                    <div class='telefono-cliente-selects'>
                        <div class="telefono-cliente-select">
                            <label>Tipo de Tel&eacute;fono:</label>
                            <select data-field="tipo" id="tipo-select" required>
                                <option value="">--Seleccionar--</option>
                                <option value="${cliente.Telefono.Tipo}" selected>${cliente.Telefono.Tipo}</option>
                            </select>
                        </div>
                        <div class="telefono-cliente-select">
                            <label>Código de Pa&iacute;s:</label>
                            <select data-field="codigo" id="codigo-select" required>
                                <option value="">--Seleccionar--</option>
                                <option value="${cliente.Telefono.CodigoPais}" selected>${cliente.Telefono.CodigoPais}</option>
                            </select>
                        </div>
                    </div>
                    <label>Número de Tel&eacute;fono:</label>
                    <input type="text" id="numero" value="${cliente.Telefono.Numero}" required>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Editar cliente",
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
            const form = document.getElementById('cliente-edit-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }

            // Obtener datos del formulario si es válido
            const nombre = document.getElementById('nombre').value || 'No Definido';
            const alias = document.getElementById('alias').value || 'No Definido';
            
            // Crear el objeto FormData
            const formData = new FormData();
            formData.append('accion', 'actualizar');
            formData.append('id', cliente.ID);
            formData.append('telefono', cliente.Telefono.ID);
            formData.append('nombre', nombre);
            formData.append('alias', alias);
            formData.append('tipo', document.getElementById('tipo-select').value);
            formData.append('codigo', document.getElementById('codigo-select').value);
            formData.append('numero', document.getElementById('numero').value);

            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            updateCliente(formData);
        }
    });
    
    initializeSelects(); // Llenar los selects después de que la fila esté preparada
    document.getElementById('numero').addEventListener('input', manejarInputNumeroTelefono); // Formatear el número de teléfono
    document.getElementById('codigo-select').addEventListener('change', manejarInputNumeroTelefono); // Actualizar al cambiar el país
}

/**
 * Abre un modal para crear un nuevo cliente.
 * 
 * @description Esta función abre un modal con un formulario para ingresar los datos de un nuevo cliente.
 *              Al confirmar, se validan los datos y se envían para crear el nuevo cliente.
 * 
 * @example
 * createCliente();
 * 
 * @returns {void}
 */
export function createCliente() {
    // Crear campos editables para los datos del cliente
    let html = `
        <div class="modal-form-container">
            <form id="cliente-create-form">
                <div class="cliente-info">
                    <label>Nombre del Cliente:</label>
                    <input type="text" id="nombre">
                </div>
                <div class="cliente-info">
                    <label>Alias:</label>
                    <input type="text" id="alias">
                </div>
                <hr>
                <div class="telefono-cliente-info">
                    <div class='telefono-cliente-selects'>
                        <div class="telefono-cliente-select">
                            <label>Tipo de Tel&eacute;fono:</label>
                            <select data-field="tipo" id="tipo-select" required>
                                <option value="">--Seleccionar--</option>
                            </select>
                        </div>
                        <div class="telefono-cliente-select">
                            <label>Código de Pa&iacute;s:</label>
                            <select data-field="codigo" id="codigo-select" required>
                                <option value="">--Seleccionar--</option>
                            </select>
                        </div>
                    </div>
                    <label>Número de Tel&eacute;fono:</label>
                    <input type="text" id="numero" required>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Crear cliente",
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
            const form = document.getElementById('cliente-create-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }

            // Obtener datos del formulario si es válido
            const nombre = document.getElementById('nombre').value || 'No Definido';
            const alias = document.getElementById('alias').value || 'No Definido';

            // Crear el objeto FormData
            const formData = new FormData();
            formData.append('accion', 'insertar');
            formData.append('nombre', nombre);
            formData.append('alias', alias);
            formData.append('tipo', document.getElementById('tipo-select').value);
            formData.append('codigo', document.getElementById('codigo-select').value);
            formData.append('numero', document.getElementById('numero').value);

            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            insertCliente(formData);
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al crear el nuevo cliente.<br>${error}`, 'error', 'Error al crear');
    });
    
    // Llenar los selects después de que la fila esté preparada
    initializeSelects();

    // Formatear el número de teléfono ingresado
    document.getElementById('numero').addEventListener('input', manejarInputNumeroTelefono);
    document.getElementById('codigo-select').addEventListener('change', manejarInputNumeroTelefono); // Da formato al número al cambiar el país
}
