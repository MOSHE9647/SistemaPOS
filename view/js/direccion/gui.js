// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza una tabla con las direcciones proporcionadas.
 * 
 * @param {Array} direcciones - Un arreglo de objetos de dirección.
 * @example
 * const direcciones = [
 *   { ID: 1, Provincia: 'San José', Canton: 'San José', Distrito: 'San José', Barrio: 'San José', Sennas: 'San José', Distancia: 0 },
 *   { ID: 2, Provincia: 'Alajuela', Canton: 'Alajuela', Distrito: 'Alajuela', Barrio: 'Alajuela', Sennas: 'Alajuela', Distancia: 10.00 },
 * ];
 * renderizarTabla(direcciones);
 */
function renderTable(direcciones) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    direcciones.forEach(direccion => {
        let row = `
            <tr data-id="${direccion.ID}">
                <td data-field="provincia">${direccion.Provincia}</td>
                <td data-field="canton">${direccion.Canton}</td>
                <td data-field="distrito">${direccion.Distrito}</td>
                <td data-field="barrio">${direccion.Barrio}</td>
                <td data-field="sennas">${direccion.Sennas}</td>
                <td data-field="distancia">${direccion.Distancia} km</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteDireccion(${direccion.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}

/**
 * Convierte una fila en editable.
 * 
 * @param {HTMLElement} row - La fila que se desea convertir en editable.
 * @description Convierte los elementos de la fila en inputs para editar los valores, y agrega botones para guardar o cancelar los cambios.
 * @example
 * makeRowEditable(document.getElementById('fila1'));
 */
function makeRowEditable(row) {
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        const field = cell.dataset.field;

        if (index < lastCellIndex) {
            if (field === 'barrio' || field === 'sennas') {
                cell.innerHTML = `<input type="text" value="${value}">`;
            } else if (field === 'distancia') {
                cell.innerHTML = `<input type="number" value="${parseFloat(value).toFixed(2)}" min="0" step="0.01" required>`;
            } else {
                cell.innerHTML = `
                    <select data-field="${field}" id="${field}-select" required>
                        <option value="${value}">${value}</option>
                    </select>
                `;
            }
        } else {
            cell.innerHTML = `
                <button onclick="updateDireccion(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit()">Cancelar</button>
            `;
        }
    });

    initializeSelects();
}

/**
 * Muestra la fila para crear una nueva dirección.
 * 
 * @description Oculta el botón de crear y agrega una nueva fila a la tabla para ingresar los datos de la dirección.
 * @example
 * showCreateRow();
 */
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="provincia">
            <select id="provincia-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="canton">
            <select id="canton-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="distrito">
            <select id="distrito-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="barrio"><input type="text"></td>
        <td data-field="sennas"><input type="text"></td>
        <td data-field="distancia"><input type="number" min="0" step="0.01" required></td>
        <td>
            <button onclick="createDireccion()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    
    // Inserta la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
    initializeSelects();
}

/**
 * Muestra un mensaje al usuario.
 * 
 * @param {string} message - El texto del mensaje que se desea mostrar.
 * @param {string} type - El tipo de mensaje (error o success).
 * @description Muestra un mensaje en la pantalla con el texto y tipo especificados, y lo oculta después de unos segundos.
 * @example
 * showMessage('Dirección creada con éxito', 'success');
 */
function showMessage(message, type) {
    let container = document.getElementById('message');
    if (container != null) {
        container.innerHTML = message;
        
        // Primero eliminamos las clases relacionadas con mensajes anteriores
        container.classList.remove('error', 'success', 'fade-out');
        
        // Agregamos las clases apropiadas según el tipo
        container.classList.add('message');
        if (type === 'error') {
            container.classList.add('error');
        } else if (type === 'success') {
            container.classList.add('success');
        }

        container.classList.add('fade-in');
        
        // Oculta el mensaje después de unos segundos
        setTimeout(() => {
            container.classList.replace('fade-in', 'fade-out');
        }, 5000); // Tiempo durante el cual el mensaje es visible
    } else {
        alert(message);
    }
}

/**
 * Cancela la edición de una dirección.
 * 
 * @description Recarga los datos de direcciones para cancelar la edición en curso.
 * @example
 * cancelEdit();
 */
function cancelEdit() {
    fetchDirecciones(currentPage, pageSize, sort); // Recargar datos para cancelar la edición
}

/**
 * Cancela la creación de una nueva dirección.
 * 
 * @description Elimina la fila de creación y muestra nuevamente el botón de crear.
 * @example
 * cancelCreate();
 */
function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block';
}