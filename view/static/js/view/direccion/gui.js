// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

import { checkEmptyTable, formatearDecimal } from "../../utils.js";
import { hideLoader, showLoader } from "../../gui/loader.js";
import { initializeSelects } from "./selects.js";
import * as crud from "./crud.js";

// Variables globales
let direcciones = [];

/**
 * Renderiza una tabla con una lista de direcciones.
 *
 * @param {Array} listaDirecciones - Lista de direcciones a mostrar en la tabla.
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se agregarán las filas.
 * @param {boolean} [isInfo=false] - Indica si la tabla es solo informativa (sin botones de acción).
 * @param {boolean} [isSearch=false] - Indica si la tabla se está renderizando como resultado de una búsqueda.
 */
export function renderTable(listaDirecciones, tableBodyID = 'tableBody', isInfo = false, isSearch = false) {
    showLoader();
    if (!isSearch) direcciones = listaDirecciones;

    cancelCreate(); // Cancelar creación de una nueva dirección
    cancelEdit(); // Cancelar edición de una dirección

    const tableBody = document.getElementById(tableBodyID);
    tableBody.innerHTML = '';

    const direccionesToRender = isSearch ? listaDirecciones : direcciones;
    console.debug('Direcciones a renderizar:', direccionesToRender);
    direccionesToRender.forEach(direccion => {
        const valorFormateado = formatearDecimal(direccion.Distancia);
        const row = document.createElement('tr');
        row.dataset.id = direccion.ID;

        row.innerHTML = `
            <td data-field="Provincia">${direccion.Provincia}</td>
            <td data-field="Canton">${direccion.Canton}</td>
            <td data-field="Distrito">${direccion.Distrito}</td>
            <td data-field="Barrio">${direccion.Barrio}</td>
            <td data-field="Sennas">${direccion.Sennas}</td>
            <td data-field="Distancia">${valorFormateado} km</td>
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
                crud.deleteDireccion(direcciones, direccion.ID);
                renderTable(direcciones, tableBodyID);
            });
        }

        tableBody.appendChild(row);
    });

    checkEmptyTable(tableBodyID, 'las la-exclamation-circle');

    if (!isInfo) {
        const btnCreate = document.getElementById('btn-create-dir');
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
        provincia: row.querySelector('[data-field="Provincia"]').textContent,
        canton: row.querySelector('[data-field="Canton"]').textContent,
        distrito: row.querySelector('[data-field="Distrito"]').textContent,
        barrio: row.querySelector('[data-field="Barrio"]').textContent,
        sennas: row.querySelector('[data-field="Sennas"]').textContent,
        distancia: row.querySelector('[data-field="Distancia"]').textContent
    });

    row.classList.add('editing-row');

    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    const fieldHandlers = {
        'barrio': (value) => `<input type="text" value="${value}">`,
        'sennas': (value) => `<input type="text" value="${value}">`,
        'distancia': (value) => `<input type="number" value="${parseFloat(value).toFixed(2)}" min="0" step="0.01" required>`
    };

    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        let field = cell.dataset.field;

        if (index < lastCellIndex) {
            field = field.toLowerCase();
            cell.innerHTML = fieldHandlers[field] ? fieldHandlers[field](value) : `
                <select data-field="${field}" id="${field}-select" required>
                    <option value="${value}">${value}</option>
                </select>
            `;
        } else {
            cell.innerHTML = `
                <button class="btn-save las la-save"></button>
                <button class="btn-cancel las la-times"></button>
            `;
        }
    });

    initializeSelects();

    row.querySelector('.btn-save').addEventListener('click', () => {
        crud.updateDireccion(direcciones, row).then((success) => {
            if (success) renderTable(direcciones, tableBodyID);
        });
    });

    row.querySelector('.btn-cancel').addEventListener('click', cancelEdit);
}

/**
 * Muestra la fila para crear una nueva dirección.
 * 
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se agregará la nueva fila.
 */
export function showCreateRow(tableBodyID = 'tableBody') {
    cancelEdit();

    const createBtn = document.getElementById('btn-create-dir');
    if (!createBtn) return;

    createBtn.style.display = 'none';

    const tableBody = document.getElementById(tableBodyID);
    const newRow = document.createElement('tr');
    newRow.classList.add('creating-row');
    newRow.innerHTML = `
        <td data-field="Provincia">
            <select id="provincia-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="Canton">
            <select id="canton-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="Distrito">
            <select id="distrito-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="Barrio"><input type="text"></td>
        <td data-field="Sennas"><input type="text"></td>
        <td data-field="Distancia"><input type="number" min="0" step="0.01" required></td>
        <td class="actions">
            <button class="btn-save las la-save"></button>
            <button class="btn-cancel las la-times"></button>
        </td>
    `;

    tableBody.insertBefore(newRow, tableBody.firstChild);

    initializeSelects();

    newRow.querySelector('.btn-save').addEventListener('click', () => {
        crud.insertDireccion(direcciones).then((success) => {
            if (success) renderTable(direcciones, tableBodyID);
        });
    });

    newRow.querySelector('.btn-cancel').addEventListener('click', cancelEdit);
}

/**
 * Cancela la edición o creación de una dirección.
 */
export function cancelEdit() {
    const editRow = document.querySelector('.editing-row');
    if (editRow && editRow.dataset.originalData) {
        const originalData = JSON.parse(editRow.dataset.originalData);

        editRow.querySelector('[data-field="Provincia"]').textContent = originalData.provincia;
        editRow.querySelector('[data-field="Canton"]').textContent = originalData.canton;
        editRow.querySelector('[data-field="Distrito"]').textContent = originalData.distrito;
        editRow.querySelector('[data-field="Barrio"]').textContent = originalData.barrio;
        editRow.querySelector('[data-field="Sennas"]').textContent = originalData.sennas;
        editRow.querySelector('[data-field="Distancia"]').textContent = originalData.distancia;

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

        const createBtn = document.getElementById('btn-create-dir');
        if (createBtn) {
            createBtn.style.display = 'block';
        }
    }
}

/**
 * Cancela la creación de una nueva dirección.
 */
export function cancelCreate() {
    const createRow = document.querySelector('.creating-row');
    if (createRow) createRow.remove();

    const createButton = document.getElementById('btn-create-dir');
    if (createButton) createButton.style.display = 'inline-block';
}

/**
 * Filtra las direcciones según el valor de búsqueda.
 * 
 * @param {string} [searchInputID='search-input'] - ID del campo de entrada de búsqueda.
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se mostrarán los resultados.
 * @param {boolean} [isInfo=false] - Indica si la tabla es solo informativa (sin botones de acción).
 */
export function searchDirecciones(searchInputID = 'search-input', tableBodyID = 'tableBody', isInfo = false) {
    const searchInput = document.getElementById(searchInputID);
    if (!searchInput) return;

    let searchValue = searchInput.value.trim().toUpperCase();
    if (searchValue === '') {
        renderTable(direcciones, tableBodyID, isInfo);
        return;
    }

    const filteredDirecciones = direcciones.filter(direccion => 
        Object.values(direccion).some(value => 
            value.toString().toUpperCase().includes(searchValue)
        )
    );
    renderTable(filteredDirecciones, tableBodyID, isInfo, true);
}

/**
 * Ordena las direcciones según el campo especificado.
 * 
 * @param {string} sortBy - Campo por el cual se ordenarán las direcciones.
 * @param {string} [tableBodyID='tableBody'] - ID del cuerpo de la tabla donde se mostrarán los resultados.
 * @param {boolean} [isInfo=false] - Indica si la tabla es solo informativa (sin botones de acción).
 */
export function sortDirecciones(sortBy, tableBodyID = 'tableBody', isInfo = false) {
    const sortedDirecciones = [...direcciones].sort((a, b) => {
        const valueA = a[sortBy] || '';
        const valueB = b[sortBy] || '';

        return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
    });

    renderTable(sortedDirecciones, tableBodyID, isInfo, true);
}
