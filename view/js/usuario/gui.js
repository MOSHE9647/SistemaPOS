// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza la tabla de usuarios con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada usuario en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el usuario.
 * 
 * @param {Array} usuarios - El arreglo de usuarios a renderizar
 * 
 * @example
 * renderTable([...]);
 * 
 * @returns {void}
 */
function renderTable(usuarios) {
    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';
    showCreateRow();

    // Recorrer cada usuario en el arreglo
    usuarios.forEach(usuario => {
        // Crear una fila para el usuario
        let row = `
            <tr data-id="${usuario.ID}">
                <td data-field="nombre">${usuario.Nombre}</td>
                <td data-field="apellido1">${usuario.Apellido1}</td>
                <td data-field="apellido2">${usuario.Apellido2}</td>
                <td data-field="correo">${usuario.Email}</td>
                <td data-field="password">Censurado</td>
                <td data-field="rol" data-id="${usuario.RolID}">${usuario.RolNombre}</td>
                <td data-field="creacion" data-iso="${usuario.CreacionISO}">${usuario.Creacion}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteUsuario(${usuario.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });

    checkEmptyTable();

    // Llenar los selects después de que la fila esté preparada
    initializeSelects();
}

/**
 * Hace editable una fila de la tabla de usuarios.
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
        apellido1: row.querySelector('[data-field="apellido1"]').textContent,
        apellido2: row.querySelector('[data-field="apellido2"]').textContent,
        correo: row.querySelector('[data-field="correo"]').textContent,
        password: row.querySelector('[data-field="password"]').textContent,
        rol: row.querySelector('[data-field="rol"]').textContent,
        creacion: row.querySelector('[data-field="creacion"]').textContent
    });

    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;
    row.setAttribute('id', 'editRow');

    const fieldHandlers = {
        'nombre': (value) => `<input type="text" value="${value}" required>`,
        'apellido1': (value) => `<input type="text" value="${value}" required>`,
        'apellido2': (value) => `<input type="text" value="${value}" required>`,
        'correo': (value) => `<input type="text" id="email" value="${value}" required>`,
        'password': (value) => `<input type="password" id="password">`,
        'creacion': (value) => `<input type="date" value="${value}" disabled>`
    };

    cells.forEach((cell, index) => {
        const value = cell.dataset.value;
        const field = cell.dataset.field;
        const text = 
            field === 'creacion' ? cell.dataset.iso : 
            (field === 'rol' ? cell.dataset.id : cell.innerText.trim())
        ;

        if (index < lastCellIndex) {
            const handler = fieldHandlers[field] || ((v, t) => `
                <select data-field="${field}" id="${field}-select" required>
                    <option value="${v}">${t}</option>
                </select>
            `);
            cell.innerHTML = value != null ? handler(value, text) : handler(text, text);
        } else {
            cell.innerHTML = `
                <button onclick="updateUsuario(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
            `;
        }
    });

    // Llenar los selects después de que la fila esté preparada
    initializeSelects();
}

/**
 * Muestra una fila para crear un nuevo usuario en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo usuario. La fila incluye botones para crear o cancelar.
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
        <td data-field="nombre"><input type="text" id="nombre" required></td>
        <td data-field="apellido1"><input type="text" id="apellido1" required></td>
        <td data-field="apellido2"><input type="text" id="apellido2" required></td>
        <td data-field="correo"><input type="email" id="correo" required></td>
        <td data-field="password"><input type="password" id="password" required></td>
        <td data-field="rol">
            <select id="rol-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="creacion"><input type="date" value="${getCurrentDate()}" disabled></td>
        <td>
            <button onclick="createUsuario()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);

    //Llenar los selects después de que la fila esté preparada
    initializeSelects();
}

/**
 * Cancela la edición de un usuario.
 * 
 * @description Esta función recarga los datos de usuarios para cancelar la edición actual.
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
        editRow.querySelector('[data-field="apellido1"]').innerHTML = originalData.apellido1;
        editRow.querySelector('[data-field="apellido2"]').innerHTML = originalData.apellido2;
        editRow.querySelector('[data-field="correo"]').innerHTML = originalData.correo;
        editRow.querySelector('[data-field="password"]').innerHTML = originalData.password;
        editRow.querySelector('[data-field="rol"]').innerHTML = originalData.rol;
        editRow.querySelector('[data-field="creacion"]').innerHTML = originalData.creacion;

        // Eliminar el atributo data-original-data
        delete editRow.dataset.originalData;
        
        // Restaurar los botones de la fila
        const cells = editRow.querySelectorAll('td');
        const lastCellIndex = cells.length - 1;
        cells[lastCellIndex].innerHTML = `
            <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
            <button onclick="deleteUsuario(${editRow.dataset.id})">Eliminar</button>
        `;

        // Eliminar el id de la fila de edición
        editRow.removeAttribute('id');
    }
}

/**
 * Cancela la creación de un nuevo usuario.
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