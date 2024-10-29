// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { updateCategoria, insertCategoria } from './crud.js';
import { mostrarMensaje } from '../../gui/notification.js';
import { checkEmptyTable } from '../../utils.js';

// Variables globales
let categorias = [];

/**
 * Renderiza la tabla de categorías con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada categoría en el arreglo,
 *              creando una fila para cada una con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar la categoría.
 * 
 * @param {Array} listaCategorias - El arreglo de categorías a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
export function renderTable(listaCategorias) {
    categorias = listaCategorias;

    const tableBodyID = 'table-categorias-body';
    const tableBody = document.getElementById(tableBodyID);

    tableBody.innerHTML = '';

    categorias.forEach(categoria => {
        const row = document.createElement('tr');
        row.setAttribute('data-id', categoria.ID);
        row.innerHTML = `
            <td data-field="nombre">${categoria.Nombre}</td>
            <td data-field="descripcion">${categoria.Descripcion}</td>
            <td class="actions">
                <button 
                    class="btn-edit las la-edit" 
                    title="Editar categoría"
                    onclick="gui.editCategoria(${categoria.ID})"
                >
                </button>
                <button 
                    class="btn-delete las la-trash" 
                    title="Eliminar categoría"
                    onclick="deleteCategoria(${categoria.ID})"
                >
                </button>
            </td>
        `;

        tableBody.appendChild(row);
    });

    checkEmptyTable(tableBodyID, 'las la-exclamation-circle');
}

/**
 * Abre un modal para editar una categoría.
 * 
 * @description Esta función abre un modal con un formulario para editar los datos de la categoría.
 *              Los campos del formulario se llenan con los datos actuales de la categoría.
 *              Al confirmar, se validan los datos y se envían para actualizar la categoría.
 * 
 * @param {number} categoriaID - El ID de la categoría a editar
 * 
 * @example
 * editCategoria(1);
 * 
 * @returns {void}
 */
export function editCategoria(categoriaID) {
    const categoria = categorias.find(categoria => categoria.ID == categoriaID);
    if (!categoria) {
        mostrarMensaje('No se encontró la categoría seleccionada.', 'error', 'Categoría no encontrada');
        return;
    }

    // Crear campos editables para los datos de la categoría
    let html = `
        <div class="modal-form-container">
            <form id="categoria-edit-form">
                <div class="categoria-info">
                    <div class="categoria-info input-select">
                        <label>Nombre de la Categoría:</label>
                        <input 
                            type="text" 
                            id="nombre" 
                            placeholder="Ej: Electrónica, Ropa, etc..." 
                            value="${categoria.Nombre}" required
                        >
                    </div>
                    <div class="categoria-info input-select">
                        <label>Descripci&oacute;n:</label>
                        <textarea 
                            id="descripcion" 
                            wrap="soft" 
                            placeholder="Escribe aquí la descripci&oacute;n de la categoría..."
                        >${categoria.Descripcion}</textarea>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Editar categoría",
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
            const form = document.getElementById('categoria-edit-form');
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los mensajes de validación del formulario
                return false; // Previene que Swal cierre si el formulario no es válido
            }

            // Obtener datos del formulario si es válido
            const formData = new FormData();
            formData.append('accion', 'actualizar');
            formData.append('id', categoriaID);
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('descripcion', document.getElementById('descripcion').value);

            return formData; // Pasar el FormData a la función `.then()`
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            updateCategoria(formData);
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al actualizar la categoría.<br>${error}`, 'error', 'Error al actualizar');
    });
}

/**
 * Abre un modal para crear una nueva categoría.
 * 
 * @description Esta función abre un modal con un formulario para ingresar los datos de una nueva categoría.
 *              Al confirmar, se validan los datos y se envían para crear la nueva categoría.
 * 
 * @example
 * createCategoria();
 * 
 * @returns {void}
 */
export function createCategoria() {
    // Crear campos editables para los datos de la categoría
    let html = `
        <div class="modal-form-container">
            <form id="categoria-edit-form">
                <div class="categoria-info">
                    <div class="categoria-info input-select">
                        <label>Nombre de la Categoría:</label>
                        <input type="text" placeholder="Ej: Electrónica, Ropa, etc..." id="nombre" required>
                    </div>
                    <div class="categoria-info input-select">
                        <label>Descripci&oacute;n:</label>
                        <textarea 
                            id="descripcion" 
                            wrap="soft" 
                            placeholder="Escribe aquí la descripci&oacute;n de la categoría..."
                        ></textarea>
                    </div>
                </div>
            </form>
        </div>
    `;

    Swal.fire({
        title: "Crear categoría",
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
            const form = document.getElementById('categoria-edit-form');
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
            insertCategoria(formData);
        }
    }).catch((error) => {
        mostrarMensaje(`Ocurrió un error al crear la nueva categoría.<br>${error}`, 'error', 'Error al crear');
    });
}
