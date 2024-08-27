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
    console.log("Proveedores a renderizar:", proveedores); // Verifica la lista de proveedores
    console.log("Proveedor actual:", proveedores); // Verifica los datos del proveedor
    console.log("tele actual:", proveedores.telefono); // Verifica los datos del proveedor


    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');
    console.log("Elemento tableBody:", tableBody);
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada proveedor en el arreglo
    proveedores.forEach(proveedor => {
        // Crear una fila para el proveedor
        let row = `
            <tr data-id="${proveedor.ID}">
                <td data-field="nombre">${proveedor.Nombre}</td>
                <td data-field="email">${proveedor.Email}</td>
                <td data-field="tipo">${proveedor.Tipo}</td>
               <td data-field="telefono">${proveedor.Telefono || 'No disponible'}</td> 
                <td data-field="fecha" data-iso="${proveedor.FechaISO}">${proveedor.Fecha}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteProveedor(${proveedor.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
        console.log("Fila agregada:", row); // Verifica la fila agregada
    });
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
    // Seleccionar todas las celdas de la fila
    const cells = row.querySelectorAll('td');
    const proveedorId = row.dataset.id; // Obtener el ID del proveedor
    
    // Definir funciones para manejar cada tipo de campo
    const fieldHandlers = {
        'fecha': (value) => {
            // Crear un campo de fecha con el valor actual y una fecha máxima igual a la fecha actual
            return `<input type="date" value="${value}" max="${getCurrentDate()}" required>`;
        },
        'nombre': (value) => {
            // Crear un campo de texto con el valor actual
            return `<input type="text" value="${value}" required>`;
        },
        'email': (value) => {
            // Crear un campo de tipo email con el valor actual y restricciones de tipo de email
            return `<input type="email" value="${value}" required>`;
        },
       'telefono': (value) => {
            // Crear un campo de combobox para los teléfonos
            return `
                <select id="telefonoSelect">
                    <option value="">Selecciona un teléfono</option>
                </select>`;
        }
        
    };

    // Recorrer cada celda de la fila
    cells.forEach((cell, index) => {
        // Excluir la última columna
        if (index < cells.length - 1) {
            // Obtener el campo y valor de la celda
            const field = cell.dataset.field;
            const value = field === 'fecha' ? cell.dataset.iso : cell.innerText;

            
            // Obtener la función de manejo para el campo o una función default
            const handler = fieldHandlers[field] || ((v) => `<input type="text" value="${v}">`);
            // Reemplazar el contenido de la celda con el campo editable
            cell.innerHTML = handler(value);
        }
    });

    // Obtener la última celda de la fila
    const actionCell = cells[cells.length - 1];
    // Reemplazar el contenido de la celda con botones para guardar o cancelar
    actionCell.innerHTML = `
        <button onclick="updateProveedor(${row.dataset.id})">Guardar</button>
        <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
    `;
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
        <td data-field="tipo"><input type="text"></td>
         <td data-field="telefono"><input type="text" required></td>
        <td data-field="fecha"><input type="date" required max="${getCurrentDate()}"></td>
        <td>
            <button onclick="createProveedor()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
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
    // Recargar datos de proveedores para cancelar la edición
    fetchProveedores(currentPage, pageSize, sort);
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
    document.getElementById('createRow').remove();

    // Mostrar el botón de crear
    document.getElementById('createButton').style.display = 'inline-block';
}