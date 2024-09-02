// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza la tabla de tipos de compra con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada tipo de compra en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el tipo de compra.
 * 
 * @param {Array} tiposCompra - El arreglo de tipos de compra a renderizar
 * 
 * @example
 * renderTable([{ tipoCompraID: 1, tipoCompraNombre: 'Compra al contado', tipoCompraTasaInteres: 0, tipoCompraDescripcion: 'Compra sin intereses', tipoCompraFechaCreacion: '2022-01-01' }]);
 * 
 * @returns {void}
 */
function renderTable(tiposCompra) {
    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada tipo de compra en el arreglo
    tiposCompra.forEach(tipoCompra => {
        // Crear una fila para el tipo de compra
        let row = `
            <tr data-id="${tipoCompra.tipoCompraID}">
                <td data-field="nombre">${tipoCompra.tipoCompraNombre}</td>
                <td data-field="tasaInteres">${tipoCompra.tipoCompraTasaInteres}%</td>
                <td data-field="descripcion">${tipoCompra.tipoCompraDescripcion}</td>
                <td data-field="fecha" data-iso="${tipoCompra.tipoCompraFechaCreacion}">${tipoCompra.tipoCompraFechaCreacion}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteTipoCompra(${tipoCompra.tipoCompraID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });
}

/**
 * Hace editable una fila de la tabla de tipos de compra.
 * 
 * @description Esta función selecciona todas las celdas de la fila y, para cada una,
 *              reemplaza su contenido con un campo de entrada editable correspondiente al tipo de dato.
 *              Los campos de fecha, nombre y tasa de interés tienen validaciones y restricciones específicas.
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
    
    // Definir funciones para manejar cada tipo de campo
    const fieldHandlers = {
        'fecha': (value) => {
            // Crear un campo de fecha con el valor actual
            return `<input type="date" value="${value}" required>`;
        },
        'nombre': (value) => {
            // Crear un campo de texto con el valor actual
            return `<input type="text" value="${value}" required>`;
        },
        'tasaInteres': (value) => {
            // Convertir el valor a double y limitar a 2 decimales
            const formattedValue = parseFloat(value).toFixed(2);
            // Crear un campo numérico con el valor formateado y restricciones de mínimo y paso
            return `<input type="number" value="${formattedValue}" min="0" step="0.01" required>`;
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
        <button onclick="updateTipoCompra(${row.dataset.id})">Guardar</button>
        <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
    `;
}

/**
 * Muestra una fila para crear un nuevo tipo de compra en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo tipo de compra. La fila incluye botones para crear o cancelar.
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
        <td data-field="tasaInteres"><input type="number" min="0" step="0.01" required></td>
        <td data-field="descripcion"><input type="text"></td>
        <td data-field="fecha"><input type="date" required></td>
        <td>
            <button onclick="createTipoCompra()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
}

/**
 * Cancela la edición de un tipo de compra.
 * 
 * @description Esta función recarga los datos de tipos de compra para cancelar la edición actual.
 * 
 * @example
 * cancelEdit();
 * 
 * @returns {void}
 */
function cancelEdit() {
    // Recargar datos de tipos de compra para cancelar la edición
    fetchTipoCompra(currentPage, pageSize, sort);
}

/**
 * Cancela la creación de un nuevo tipo de compra.
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
