// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { checkEmptyTable, manejarInputNumeroTelefono } from "../../utils.js";
import { hideLoader, showLoader } from "../../gui/loader.js";
import { initializeSelects } from "./selects.js";
import * as crud from "./crud.js";

// Variables globales
let telefonos = [];

/**
 * Renderiza una tabla con una lista de teléfonos.
 *
 * @param {Array} listaTelefonos - Lista de teléfonos a mostrar en la tabla.
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se agregarán las filas.
 * @param {boolean} [isInfo=false] - Indica si la tabla es solo informativa (sin botones de acción).
 * @param {boolean} [isSearch=false] - Indica si la tabla se está renderizando como resultado de una búsqueda.
 */
export function renderTable(listaTelefonos, tableBodyID = 'tableBody', isInfo = false, isSearch = false) {
    showLoader();
    if (!isSearch) telefonos = listaTelefonos;
    
    cancelCreate(); // Cancelar la creación de un nuevo teléfono
    cancelEdit(); // Cancelar la edición de un teléfono

    const tableBody = document.getElementById(tableBodyID);
    tableBody.innerHTML = '';

    const telefonosToRender = isSearch ? listaTelefonos : telefonos;
    telefonosToRender.forEach(telefono => {
        const row = document.createElement('tr');
        row.dataset.id = telefono.ID;

        row.innerHTML = `
            <td data-field="Tipo">${telefono.Tipo}</td>
            <td data-field="CodigoPais">${telefono.CodigoPais}</td>
            <td data-field="Numero">${telefono.Numero}</td>
            <td data-field="Extension">${telefono.Extension}</td>
        `;

        if (!isInfo) {
            const actionsCell = document.createElement('td');
            actionsCell.classList.add('actions');
            actionsCell.innerHTML = `
                <button class="btn-edit las la-edit"></button>
                <button class="btn-delete las la-trash"></button>
            `;
            row.appendChild(actionsCell);

            row.querySelector('.btn-edit').addEventListener('click', () => makeRowEditable(row, tableBodyID));
            row.querySelector('.btn-delete').addEventListener('click', () => {
                crud.deleteTelefono(telefonos, telefono.ID);
                renderTable(telefonos, tableBodyID);
            });
        }

        tableBody.appendChild(row);
    });

    checkEmptyTable(tableBodyID, 'las la-exclamation-circle');

    if (!isInfo) {
        const btnCreate = document.getElementById('btn-create-tel');
        if (btnCreate) btnCreate.addEventListener('click', () => showCreateRow(tableBodyID));
    }

    hideLoader();
}

/**
 * Convierte una fila en editable.
 * 
 * @param {HTMLElement} row - La fila que se desea convertir en editable.
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se encuentra la fila.
 */
export function makeRowEditable(row, tableBodyID = 'tableBody') {
    cancelCreate();
    cancelEdit();

    row.dataset.originalData = JSON.stringify({
        tipo: row.querySelector('[data-field="Tipo"]').textContent,
        codigoPais: row.querySelector('[data-field="CodigoPais"]').textContent,
        numero: row.querySelector('[data-field="Numero"]').textContent,
        extension: row.querySelector('[data-field="Extension"]').textContent
    });

    row.classList.add('editing-row');

    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    const fieldHandlers = {
        'numero': (value) => `<input type="text" id="numero" value="${value}" required>`,
        'extension': (value) => `<input type="text" value="${value}">`
    };
    
    cells.forEach((cell, index) => {
        const value = cell.dataset.value;
        const field = cell.dataset.field;
        const text = cell.innerText.trim();
    
        if (index < lastCellIndex) {
            const handler = fieldHandlers[field] || ((v, t) => `
                <select data-field="${field}" id="${field}-select" required>
                    <option value="${v}">${t}</option>
                </select>
            `);
            cell.innerHTML = value != null ? handler(value, text) : handler(text, text);
        } else {
            cell.innerHTML = `
                <button class="btn-save las la-save"></button>
                <button class="btn-cancel las la-times"></button>
            `;
        }
    });

    initializeSelects();

    row.querySelector('.btn-save').addEventListener('click', () => {
        if (crud.updateTelefono(telefonos, row.dataset.id)) {
            renderTable(telefonos, tableBodyID);
        }
    });
    row.querySelector('.btn-cancel').addEventListener('click', cancelEdit);
    
    // Formatear el número de teléfono ingresado
    row.getElementById('numero').addEventListener('input', manejarInputNumeroTelefono);
    row.getElementById('codigo-select').addEventListener('change', manejarInputNumeroTelefono); // Actualizar al cambiar el país
}

/**
 * Muestra la fila para crear un nuevo teléfono.
 * 
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se agregará la nueva fila.
 */
export function showCreateRow(tableBodyID = 'tableBody') {
    cancelEdit();

    const createBtn = document.getElementById('btn-create-tel');
    if (!createBtn) return;

    createBtn.style.display = 'none';

    const tableBody = document.getElementById(tableBodyID);
    const newRow = document.createElement('tr');
    newRow.classList.add('creating-row');
    newRow.innerHTML = `
        <td data-field="Tipo">
            <select id="tipo-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="CodigoPais">
            <select id="codigo-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="Numero"><input type="text" id="numero" required></td>
        <td data-field="Extension"><input type="text"></td>
        <td class="actions">
            <button class="btn-save las la-save"></button>
            <button class="btn-cancel las la-times"></button>
        </td>
    `;

    tableBody.insertBefore(newRow, tableBody.firstChild);

    initializeSelects();

    newRow.querySelector('.btn-save').addEventListener('click', () => {
        if (crud.insertTelefono(telefonos)) {
            renderTable(telefonos, tableBodyID);
        }
    });

    newRow.querySelector('.btn-cancel').addEventListener('click', cancelEdit);

    // Formatear el número de teléfono ingresado
    newRow.querySelector('#numero').addEventListener('input', manejarInputNumeroTelefono);
    newRow.querySelector('#codigo-select').addEventListener('change', manejarInputNumeroTelefono); // Actualizar al cambiar el país
}

/**
 * Cancela la edición o creación de un teléfono.
 */
export function cancelEdit() {
    const editRow = document.querySelector('.editing-row');
    if (editRow && editRow.dataset.originalData) {
        const originalData = JSON.parse(editRow.dataset.originalData);

        editRow.querySelector('[data-field="Tipo"]').textContent = originalData.tipo;
        editRow.querySelector('[data-field="CodigoPais"]').textContent = originalData.codigoPais;
        editRow.querySelector('[data-field="Numero"]').textContent = originalData.numero;
        editRow.querySelector('[data-field="Extension"]').textContent = originalData.extension;

        delete editRow.dataset.originalData;

        const cells = editRow.querySelectorAll('td');
        const lastCellIndex = cells.length - 1;
        cells[lastCellIndex].innerHTML = `
            <button class="btn-edit las la-edit"></button>
            <button class="btn-delete las la-trash"></button>
        `;

        editRow.classList.remove('editing-row');
    }

    const createRow = document.querySelector('.creating-row');
    if (createRow) {
        createRow.remove();

        const createBtn = document.getElementById('btn-create-tel');
        if (createBtn) {
            createBtn.style.display = 'block';
        }
    }
}

/**
 * Cancela la creación de un nuevo teléfono.
 */
export function cancelCreate() {
    const createRow = document.querySelector('.creating-row');
    if (createRow) createRow.remove();

    const createButton = document.getElementById('btn-create-tel');
    if (createButton) createButton.style.display = 'inline-block';
}

/**
 * Filtra los teléfonos según el valor de búsqueda.
 * 
 * @param {string} [searchInputID='search-input'] - ID del campo de entrada de búsqueda.
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se mostrarán los resultados.
 * @param {boolean} [isInfo=false] - Indica si la tabla es solo informativa (sin botones de acción).
 */
export function searchTelefonos(searchInputID = 'search-input', tableBodyID = 'tableBody', isInfo = false) {
    const searchInput = document.getElementById(searchInputID);
    if (!searchInput) return;

    let searchValue = searchInput.value.trim().toUpperCase();
    if (searchValue === '') {
        renderTable(telefonos, tableBodyID);
        return;
    }

    const filteredTelefonos = telefonos.filter(telefono => 
        Object.values(telefono).some(value => 
            value.toString().toUpperCase().includes(searchValue)
        )
    );
    renderTable(filteredTelefonos, tableBodyID, isInfo, true);
}

/**
 * Ordena los teléfonos según el campo especificado.
 * 
 * @param {string} sortBy - Campo por el cual se ordenarán los teléfonos.
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se mostrarán los resultados.
 * @param {boolean} [isInfo=false] - Indica si la tabla es solo informativa (sin botones de acción).
 */
export function sortTelefonos(sortBy, tableBodyID = 'tableBody', isInfo = false) {
    const sortedTelefonos = [...telefonos].sort((a, b) => {
        const valueA = a[sortBy] || '';
        const valueB = b[sortBy] || '';

        return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
    });

    renderTable(sortedTelefonos, tableBodyID, isInfo, true);
}
