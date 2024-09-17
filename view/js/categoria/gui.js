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
                <td data-field="nombre">${rol.Nombre}</td>
                <td data-field="descripcion">${rol.Descripcion}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteRol(${rol.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    checkEmptyTable();
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
        nombre: row.querySelector('[data-field="nombre"]').textContent,
        descripcion: row.querySelector('[data-field="descripcion"]').textContent,
    });

    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;
    row.setAttribute('id', 'editRow');

    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        const field = cell.dataset.field;

        if (index < lastCellIndex) {
            const required = field === 'nombre' ? 'required' : '';
            const handler = ((v) => `<input type="text" value="${v}" ${required}>`);
            cell.innerHTML = handler(value);
        } else {
            cell.innerHTML = `
                <button onclick="updateRol(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
            `;
        }
    });
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
        <td data-field="nombre"><input type="text" required></td>
        <td data-field="descripcion"><input type="text"></td>
        <td>
            <button onclick="createRol()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
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

        editRow.querySelector('[data-field="nombre"]').innerHTML = originalData.nombre;
        editRow.querySelector('[data-field="descripcion"]').innerHTML = originalData.descripcion;

        // Eliminar el atributo data-original-data
        delete editRow.dataset.originalData;
        
        // Restaurar los botones de la fila
        const cells = editRow.querySelectorAll('td');
        const lastCellIndex = cells.length - 1;
        cells[lastCellIndex].innerHTML = `
            <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
            <button onclick="deleteRol(${editRow.dataset.id})">Eliminar</button>
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