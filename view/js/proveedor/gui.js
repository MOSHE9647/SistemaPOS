// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza la tabla de proveedores con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada proveedor en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el proveedor.
 * 
 * @param {Array} proveedores - El arreglo de proveedores a renderizar
 * 
 * @example
 * renderTable([...]); //<- Lista de Proveedores
 * 
 * @returns {void}
 */
function renderTable(proveedores) {
    let tableBody = document.getElementById('tableBody'); // Obtener el cuerpo de la tabla
    tableBody.innerHTML = ''; // Vaciar el cuerpo de la tabla

    // Recorrer cada proveedor en el arreglo
    proveedores.forEach(proveedor => {
        // Crear una fila para el proveedor
        let row = `
            <tr data-id="${proveedor.ID}">
                <td data-field="nombre">${proveedor.Nombre}</td>
                <td data-field="email">${proveedor.Email}</td>
                <td data-field="categoria" data-id="${proveedor.CategoriaID}">${proveedor.CategoriaNombre}</td>
                <td data-field="creacion" data-iso="${proveedor.FechaISO}">${proveedor.Fecha}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteProveedor(${proveedor.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    // Verificar si la tabla está vacía
    checkEmptyTable();
}

/**
 * Hace editable una fila de la tabla de proveedores.
 * 
 * @description Esta función selecciona todas las celdas de la fila y, para cada una,
 *              reemplaza su contenido con un campo de entrada editable correspondiente al tipo de dato.
 *              Los campos de fecha, nombre y email tienen validaciones y restricciones específicas.
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
        nombre: row.querySelector('[data-field="nombre"]').textContent,
        email: row.querySelector('[data-field="email"]').textContent,
        categoria: row.querySelector('[data-field="categoria"]').textContent,
        creacion: row.querySelector('[data-field="creacion"]').textContent
    });

    // Obtener las celdas de la fila
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;
    row.setAttribute('id', 'editRow');

    // Definir los manejadores de campos para cada tipo de dato
    const fieldHandlers = {
        'nombre': (value) => `<input type="text" id="nombre" value="${value}" required>`,
        'email': (value) => `<input type="email" id="email" value="${value}" required>`,
        'creacion': (value) => `<input type="date" value="${value}" disabled>`
    };

    // Recorrer cada celda de la fila
    cells.forEach((cell, index) => {
        const value = cell.dataset.value;
        const field = cell.dataset.field;
        const text = 
            field === 'creacion' ? cell.dataset.iso : 
            (field === 'categoria' ? cell.dataset.id : cell.innerText.trim());

        if (index < lastCellIndex) {
            const handler = fieldHandlers[field] || ((v, t) => `
                <select data-field="${field}" id="${field}-select" required>
                    <option value="${v}">${t}</option>
                </select>
            `);
            cell.innerHTML = value != null ? handler(value, text) : handler(text, text);
        } else {
            cell.innerHTML = `
                <button onclick="updateProveedor(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
            `;
        }
    });

    // Llenar los selects después de que la fila esté preparada
    initializeSelects();
}

/**
 * Muestra una fila para crear un nuevo proveedor en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo proveedor. La fila incluye botones para crear o cancelar.
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
        <td data-field="nombre"><input type="text" required></td>
        <td data-field="email"><input type="email" required></td>
        <td data-field="categoria">
            <select id="categoria-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="creacion"><input type="date" value="${getCurrentDate()}" disabled></td>
        <td>
            <button onclick="createProveedor()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);

    //Llenar los selects después de que la fila esté preparada
    initializeSelects();
}

/**
 * Cancela la edición de un proveedor.
 * 
 * @description Esta función recarga los datos de proveedores para cancelar la edición actual.
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

        editRow.querySelector('[data-field="nombre"]').innerHTML = originalData.nombre;
        editRow.querySelector('[data-field="email"]').innerHTML = originalData.email;
        editRow.querySelector('[data-field="categoria"]').innerHTML = originalData.categoria;
        editRow.querySelector('[data-field="creacion"]').innerHTML = originalData.creacion;

        // Eliminar el atributo data-original-data
        delete editRow.dataset.originalData;
        
        // Restaurar los botones de la fila
        const cells = editRow.querySelectorAll('td');
        const lastCellIndex = cells.length - 1;
        cells[lastCellIndex].innerHTML = `
            <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
            <button onclick="deleteProveedor(${editRow.dataset.id})">Eliminar</button>
        `;

        // Eliminar el id de la fila de edición
        editRow.removeAttribute('id');
    }
}

/**
 * Cancela la creación de un nuevo proveedor.
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