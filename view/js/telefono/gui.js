// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza la tabla de telefonos con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada telefono en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el telefono.
 * 
 * @param {Array} telefonos - El arreglo de telefonos a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
function renderTable(telefonos) {
    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada telefono en el arreglo
    telefonos.forEach(telefono => {
        // Crear una fila para el telefono
        let row = `
            <tr data-id="${telefono.ID}">
                <td data-field="proveedor" data-value="${telefono.Proveedor.ID}" >${telefono.Proveedor.Nombre}</td>
                <td data-field="tipo">${telefono.Tipo}</td>
                <td data-field="codigo">${telefono.CodigoPais}</td>
                <td data-field="numero">${telefono.Numero}</td>
                <td data-field="extension">${telefono.Extension}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteTelefono(${telefono.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });
}

/**
 * Hace editable una fila de la tabla de telefonos.
 * 
 * @description Esta función selecciona todas las celdas de la fila y, para cada una,
 *              reemplaza su contenido con un campo de entrada editable correspondiente al tipo de dato.
 *              Los campos de fecha, nombre y valor tienen validaciones y restricciones específicas.
 *              La última celda se reemplaza con botones para guardar o cancelar los cambios.
 * 
 * @param {HTMLElement} row - La fila de la tabla a hacer editable
 * 
 * @example
 * makeRowEditable(document.querySelector('tr[data-id="1"]'));
 * 
 * @returns {void}
 */
function makeRowEditable(row) {
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    const fieldHandlers = {
        'numero': (value) => `<input type="text" id="numero" value="${value}" required>`,
        'extension': (value) => `<input type="text" value="${value}">`
    };

    cells.forEach((cell, index) => {
        const text = cell.innerText.trim();
        const value = cell.dataset.value;
        const field = cell.dataset.field;

        if (index < lastCellIndex) {
            const handler = fieldHandlers[field] || ((v, t) => `
                <select data-field="${field}" id="${field}-select" required>
                    <option value="${v}">${t}</option>
                </select>
            `);
            cell.innerHTML = value != null ? handler(value, text) : handler(text, text);
        } else {
            cell.innerHTML = `
                <button onclick="updateTelefono(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
            `;
        }
    });

    // Llenar los selects después de que la fila esté preparada
    initializeSelects();

    // Formatear el número de teléfono ingresado
    document.getElementById('numero').addEventListener('input', manejarInput);
    document.getElementById('codigo-select').addEventListener('change', manejarInput); // Actualizar al cambiar el país
}

/**
 * Muestra una fila para crear un nuevo telefono en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo telefono. La fila incluye botones para crear o cancelar.
 * 
 * @example
 * showCreateRow();
 * 
 * @returns {void}
 */
function showCreateRow() {
    // Ocultar el botón de crear
    document.getElementById('createButton').style.display = 'none';

    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');

    // Crear una nueva fila para la tabla
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    // Definir el contenido de la fila con campos editables
    newRow.innerHTML = `
        <td data-field="proveedor">
            <select id="proveedor-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="tipo">
            <select id="tipo-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="codigo">
            <select id="codigo-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="numero"><input type="text" id="numero" required></td>
        <td data-field="extension"><input type="text"></td>
        <td>
            <button onclick="createTelefono()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);

    //Llenar los selects después de que la fila esté preparada
    initializeSelects();

    // Formatear el número de teléfono ingresado
    document.getElementById('numero').addEventListener('input', manejarInput);
    document.getElementById('codigo-select').addEventListener('change', manejarInput); // Actualizar al cambiar el país
}

/**
 * Cancela la edición de un telefono.
 * 
 * @description Esta función recarga los datos de telefonos para cancelar la edición actual.
 * 
 * @example
 * cancelEdit();
 * 
 * @returns {void}
 */
function cancelEdit() {
    // Recargar datos de telefonos para cancelar la edición
    fetchTelefonos(currentPage, pageSize, sort);
}

/**
 * Cancela la creación de un nuevo telefono.
 * 
 * @description Esta función elimina la fila de creación y muestra el botón de crear.
 * 
 * @example
 * cancelCreate();
 * 
 * @returns {void}
 */
function cancelCreate() {
    // Eliminar la fila de creación
    document.getElementById('createRow').remove();

    // Mostrar el botón de crear
    document.getElementById('createButton').style.display = 'inline-block';
}