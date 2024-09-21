// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza la tabla de subcategorias con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada subcategoria en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el subcategoria.
 * 
 * @param {Array} subcategorias - El arreglo de subcategorias a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
function renderTable(subcategorias) {
    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';
    showCreateRow();

    // Recorrer cada subcategoria en el arreglo
    subcategorias.forEach(subcategoria => {
        // Crear una fila para el subcategoria
        let row = `
            <tr data-id="${subcategoria.ID}">
                <td data-field="nombre">${subcategoria.Nombre}</td>
                <td data-field="categoria" data-id="${subcategoria.CategoriaID}">${subcategoria.Categoria}</td>
                <td data-field="descripcion">${subcategoria.Descripcion}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteSubcategoria(${subcategoria.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    checkEmptyTable();
}

/**
 * Hace editable una fila de la tabla de subcategorias.
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
        nombre: row.querySelector('[data-field="nombre"]').textContent,
        categoria: row.querySelector('[data-field="categoria"]').textContent,
        descripcion: row.querySelector('[data-field="descripcion"]').textContent,
    });

    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;
    row.setAttribute('id', 'editRow');

    // Definir los manejadores de campos para cada tipo de dato
    const fieldHandlers = {
        'nombre': (value) => `<input type="text" id="nombre" value="${value}" required>`,
        'descripcion': (value) => `<input type="text" id="descripcion" value="${value}">`,
    };

    cells.forEach((cell, index) => {
        const value = cell.dataset.value;
        const field = cell.dataset.field;
        const text = field === 'categoria' ? cell.dataset.id : cell.innerText.trim();

        if (index < lastCellIndex) {
            const handler = fieldHandlers[field] || ((v, t) => `
                <select data-field="${field}" id="${field}-select" required>
                    <option value="${v}">${t}</option>
                </select>
            `);
            cell.innerHTML = value != null ? handler(value, text) : handler(text, text);
        } else {
            cell.innerHTML = `
                <button onclick="updateSubcategoria(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
            `;
        }
    });
    
    // Llenar los selects después de que la fila esté preparada
    initializeSelects();
}

/**
 * Muestra una fila para crear un nuevo subcategoria en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo subcategoria. La fila incluye botones para crear o cancelar.
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
        <td data-field="categoria">
            <select id="categoria-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="descripcion"><input type="text"></td>
        <td>
            <button onclick="createSubcategoria()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);

    // Llenar los selects después de que la fila esté preparada
    initializeSelects();
}

/**
 * Cancela la edición de un subcategoria.
 * 
 * @description Esta función recarga los datos de subcategorias para cancelar la edición actual.
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
        editRow.querySelector('[data-field="categoria"]').innerHTML = originalData.categoria;
        editRow.querySelector('[data-field="descripcion"]').innerHTML = originalData.descripcion;

        // Eliminar el atributo data-original-data
        delete editRow.dataset.originalData;
        
        // Restaurar los botones de la fila
        const cells = editRow.querySelectorAll('td');
        const lastCellIndex = cells.length - 1;
        cells[lastCellIndex].innerHTML = `
            <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
            <button onclick="deleteSubcategoria(${editRow.dataset.id})">Eliminar</button>
        `;

        // Eliminar el id de la fila de edición
        editRow.removeAttribute('id');
    }
}

/**
 * Cancela la creación de un nuevo subcategoria.
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