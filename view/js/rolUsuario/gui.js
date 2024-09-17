// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza la tabla de roles con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada rol en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el rol.
 * 
 * @param {Array} roles - El arreglo de roles a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
function renderTable(roles) {
    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';
    showCreateRow();

    // Recorrer cada rol en el arreglo
    roles.forEach(rol => {
        // Crear una fila para el rol
        let row = `
            <tr data-id="${rol.ID}">
                <td data-field="tipo">${rol.Tipo}</td>
                <td data-field="codigo">${rol.CodigoPais}</td>
                <td data-field="numero">${rol.Numero}</td>
                <td data-field="extension">${rol.Extension}</td>
                <td data-field="creacion" data-iso="${rol.CreacionISO}">${rol.Creacion}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteTelefono(${rol.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    checkEmptyTable();

    // Llenar los selects después de que la fila esté preparada
    initializeSelects();

    // Formatear el número de rol ingresado
    document.getElementById('numero').addEventListener('input', manejarInput);
    document.getElementById('codigo-select').addEventListener('change', manejarInput); // Actualizar al cambiar el país
}

/**
 * Hace editable una fila de la tabla de roles.
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
    cancelCreate();
    cancelEdit();

    // Almacenar los datos originales en un atributo data
    row.dataset.originalData = JSON.stringify({
        tipo: row.querySelector('[data-field="tipo"]').textContent,
        codigo: row.querySelector('[data-field="codigo"]').textContent,
        numero: row.querySelector('[data-field="numero"]').textContent,
        extension: row.querySelector('[data-field="extension"]').textContent,
        creacion: row.querySelector('[data-field="creacion"]').textContent
    });

    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;
    row.setAttribute('id', 'editRow');

    const fieldHandlers = {
        'numero': (value) => `<input type="text" id="numero" value="${value}" required>`,
        'creacion': (value) => `<input type="date" value="${value}" disabled>`,
        'extension': (value) => `<input type="text" value="${value}">`
    };

    cells.forEach((cell, index) => {
        const value = cell.dataset.value;
        const field = cell.dataset.field;
        const text = field === 'creacion' ? cell.dataset.iso : cell.innerText.trim();

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

    // Formatear el número de rol ingresado
    document.getElementById('numero').addEventListener('input', manejarInput);
    document.getElementById('codigo-select').addEventListener('change', manejarInput); // Actualizar al cambiar el país
}

/**
 * Muestra una fila para crear un nuevo rol en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo rol. La fila incluye botones para crear o cancelar.
 * 
 * @example
 * showCreateRow();
 * 
 * @returns {void}
 */
function showCreateRow() {
    cancelEdit();

    // Ocultar el botón de crear
    document.getElementById('createButton').style.display = 'none';

    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');

    // Crear una nueva fila para la tabla
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    // Definir el contenido de la fila con campos editables
    newRow.innerHTML = `
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
        <td data-field="creacion"><input type="date" value="${getCurrentDate()}" disabled></td>
        <td>
            <button onclick="createTelefono()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);

    //Llenar los selects después de que la fila esté preparada
    initializeSelects();

    // Formatear el número de rol ingresado
    document.getElementById('numero').addEventListener('input', manejarInput);
    document.getElementById('codigo-select').addEventListener('change', manejarInput); // Actualizar al cambiar el país
}

/**
 * Cancela la edición de un rol.
 * 
 * @description Esta función recarga los datos de roles para cancelar la edición actual.
 * 
 * @example
 * cancelEdit();
 * 
 * @returns {void}
 */
function cancelEdit() {
    // Restaurar los datos originales de la fila de edición
    const editRow = document.getElementById('editRow');
    if (editRow && editRow.dataset.originalData) {
        const originalData = JSON.parse(editRow.dataset.originalData);

        editRow.querySelector('[data-field="tipo"]').innerHTML = originalData.tipo;
        editRow.querySelector('[data-field="codigo"]').innerHTML = originalData.codigo;
        editRow.querySelector('[data-field="numero"]').innerHTML = originalData.numero;
        editRow.querySelector('[data-field="extension"]').innerHTML = originalData.extension;
        editRow.querySelector('[data-field="creacion"]').innerHTML = originalData.creacion;

        // Eliminar el atributo data-original-data
        delete editRow.dataset.originalData;
        
        // Restaurar los botones de la fila
        const cells = editRow.querySelectorAll('td');
        const lastCellIndex = cells.length - 1;
        cells[lastCellIndex].innerHTML = `
            <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
            <button onclick="deleteTelefono(${editRow.dataset.id})">Eliminar</button>
        `;

        // Eliminar el id de la fila de edición
        editRow.removeAttribute('id');
    }
}

/**
 * Cancela la creación de un nuevo rol.
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
    const createRow = document.getElementById('createRow');
    if (createRow) createRow.remove();

    // Mostrar el botón de crear
    const createButton = document.getElementById('createButton');
    if (createButton) createButton.style.display = 'inline-block';
}