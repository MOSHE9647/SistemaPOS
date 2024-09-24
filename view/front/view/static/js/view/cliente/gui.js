import { showLoader, hideLoader } from '../../gui/loader.js';
import { mostrarMensaje } from '../../gui/notification.js';
import { checkEmptyTable, manejarInputNumeroTelefono } from '../../utils.js';
import { initializeSelects } from '../telefono/selects.js';
import { updateCliente, insertCliente } from './crud.js';
import 'https://cdn.jsdelivr.net/npm/sweetalert2@11';

// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

// Variables globales
let clientes;

/**
 * Renderiza la tabla de clientes con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada cliente en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el cliente.
 * 
 * @param {Array} clientes - El arreglo de clientes a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
export function renderTable(listaClientes) {
    clientes = listaClientes;

    // Obtener el cuerpo de la tabla
    let tableBodyID = 'table-clientes-body';
    let tableBody = document.getElementById(tableBodyID);
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';
    // showCreateRow();

    // Recorrer cada cliente en el arreglo
    clientes.forEach(cliente => {
        // Obtener la información del teléfono y del cliente
        const codigoPais = cliente.Telefono.CodigoPais;
        const numero = cliente.Telefono.Numero;
        const extension = cliente.Telefono.Extension ? `ext. ${cliente.Telefono.Extension}` : '';
        const telefonoCompleto = `${codigoPais} ${numero} ${extension}`;

        // Crear una fila para el cliente
        let row = `
            <tr data-id="${cliente.ID}">
            <td data-field="nombre">${cliente.Nombre}</td>
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

    checkEmptyTable(tableBodyID, 'las la-user-times');
}

/**
 * Hace editable una fila de la tabla de clientes.
 * 
 * @description Esta función selecciona todas las celdas de la fila y, para cada una,
 *              reemplaza su contenido con un campo de entrada editable correspondiente al tipo de dato.
 *              Los campos de fecha, nombre y valor tienen validaciones y restricciones específicas.
 *              La última celda se reemplaza con botones para guardar o cancelar los cambios.
 * 
 * @param {HTMLElement} row - La fila de la tabla a hacer editable
 * 
 * @example
 * editCliente(1);
 * 
 * @returns {void}
 */
export function editCliente(clienteID) {
    const cliente = clientes.find(cliente => cliente.ID == clienteID);
    if (!cliente) mostrarMensaje('No se encontró el cliente solicitado.', 'error', 'Cliente no encontrado');

    // Crear campos editables para los datos del cliente
    let html = `
        <div class="modal-form-container">
            <form id="cliente-edit-form">
                <div class="cliente-info">
                    <label>Nombre del Cliente:</label>
                    <input type="text" id="nombre" value="${cliente.Nombre}">
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
            const id = cliente.ID;
            const nombre = document.getElementById('nombre').value;
            const tipo = document.getElementById('tipo-select').value;
            const codigo = document.getElementById('codigo-select').value;
            const numero = document.getElementById('numero').value;

            // Crear un objeto con los datos del cliente
            const telefonoID = cliente.Telefono.ID;
            const clienteData = { "id": id, "nombre": nombre, "telefono": telefonoID };
            const telefonoData = { "id": telefonoID, "tipo": tipo, "codigo": codigo, "numero": numero };
            const data = [clienteData, telefonoData];
            return data; // Pasar los datos a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            showLoader();
            updateCliente(formData);
        }
    });
    
    // Llenar los selects después de que la fila esté preparada
    initializeSelects();

    // Formatear el número de teléfono ingresado
    document.getElementById('numero').addEventListener('input', manejarInputNumeroTelefono);
    document.getElementById('codigo-select').addEventListener('change', manejarInputNumeroTelefono); // Actualizar al cambiar el país
}

/**
 * Muestra una fila para crear un nuevo cliente en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo cliente. La fila incluye botones para crear o cancelar.
 * 
 * @example
 * showCreateRow();
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
            const nombre = document.getElementById('nombre').value;
            const tipo = document.getElementById('tipo-select').value;
            const codigo = document.getElementById('codigo-select').value;
            const numero = document.getElementById('numero').value;

            // Crear un objeto con los datos del cliente
            const clienteData = { "nombre": nombre, };
            const telefonoData = { "tipo": tipo, "codigo": codigo, "numero": numero };
            const data = [clienteData, telefonoData];
            return data; // Pasar los datos a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            showLoader();
            insertCliente(formData);
            hideLoader();
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al crear el nuevo cliente.<br>${error}`, 'error', 'Error al crear');
        hideLoader(); // Ocultar el loader
    });
    
    // Llenar los selects después de que la fila esté preparada
    initializeSelects();

    // Formatear el número de teléfono ingresado
    document.getElementById('numero').addEventListener('input', manejarInputNumeroTelefono);
    document.getElementById('codigo-select').addEventListener('change', manejarInputNumeroTelefono); // Actualizar al cambiar el país
}