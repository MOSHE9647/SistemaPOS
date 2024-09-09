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

    // Recorrer cada subcategoria en el arreglo
    subcategorias.forEach(subcategoria => {
        // Crear una fila para el subcategoria
        let row = `
            <tr data-id="${subcategoria.ID}">
                <td data-field="nombre">${subcategoria.Nombre}</td>
                <td data-field="descripcion">${subcategoria.Descripcion}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="removeSubcategoriaFromProducto(${subcategoria.ID})">Eliminar</button>
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
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    const fieldHandlers = {
        'nombre': (value) => `<input type="text" value="${value}" required>`
    };

    cells.forEach((cell, index) => {
        const field = cell.dataset.field;
        const value = cell.innerText;

        if (index < lastCellIndex) {
            const handler = fieldHandlers[field] || ((v) => `<input type="text" value="${v}">`);
            cell.innerHTML = handler(value);
        } else {
            cell.innerHTML = `
                <button onclick="updateSubcategoria(${row.dataset.id})">Guardar</button>
                <button id="cancelEdit" onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
            `;
        }
    });
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
        <td data-field="descripcion"><input type="text"></td>
        <td>
            <button onclick="addSubcategoriaToProducto()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
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
    // Recargar datos de subcategorias para cancelar la edición
    const cancelBtn = document.getElementById('cancelEdit');
    if (cancelBtn) { fetchSubcategorias(producto, currentPage, pageSize, sort); }
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
    if (createRow) { createRow.remove(); }

    // Mostrar el botón de crear
    const createBtn = document.getElementById('createButton');
    if (createBtn) { createBtn.style.display = 'inline-block'; }
}