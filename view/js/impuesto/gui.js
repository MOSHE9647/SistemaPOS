// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza la tabla de impuestos con los datos proporcionados.
 * 
 * @description Esta función vacía el cuerpo de la tabla y luego recorre cada impuesto en el arreglo,
 *              creando una fila para cada uno con los datos correspondientes.
 *              Cada fila incluye botones para editar y eliminar el impuesto.
 * 
 * @param {Array} impuestos - El arreglo de impuestos a renderizar
 * 
 * @example
 * renderTable([{ ID: 1, Nombre: 'Impuesto 1', Valor: 10, Descripcion: 'Descripción 1', VigenciaISO: '2022-01-01', Vigencia: '01 ene. 2022' }]);
 * 
 * @returns {void}
 */
function renderTable(impuestos) {
    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; //<- Vaciar el cuerpo de la tabla
    showCreateRow(); //<- Mostrar la fila para crear un nuevo impuesto

    // Recorrer cada impuesto en el arreglo
    impuestos.forEach(impuesto => {
        // Formatear el valor para que solo muestre decimales si es necesario
        let valorFormateado = formatearDecimal(impuesto.Valor);

        // Crear una fila para el impuesto
        let row = `
            <tr data-id="${impuesto.ID}">
                <td data-field="nombre">${impuesto.Nombre}</td>
                <td data-field="valor">${valorFormateado}%</td>
                <td data-field="descripcion">${impuesto.Descripcion}</td>
                <td data-field="fechaInicio" data-iso="${impuesto.InicioVigenciaISO}">${impuesto.InicioVigencia}</td>
                <td data-field="fechaFin" data-iso="${impuesto.FinVigenciaISO}">${impuesto.FinVigencia}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteImpuesto(${impuesto.ID})">Eliminar</button>
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
 * Hace editable una fila de la tabla de impuestos.
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
    cancelCreate(); // Cancelar la creación de un nuevo impuesto
    cancelEdit(); // Cancelar la edición de un impuesto existente

    // Almacenar los datos originales en un atributo data
    row.dataset.originalData = JSON.stringify({
        nombre: row.querySelector('[data-field="nombre"]').textContent,
        valor: row.querySelector('[data-field="valor"]').textContent,
        descripcion: row.querySelector('[data-field="descripcion"]').textContent,
        fechaInicio: row.querySelector('[data-field="fechaInicio"]').dataset.iso,
        fechaFin: row.querySelector('[data-field="fechaFin"]').dataset.iso
    });

    // Seleccionar todas las celdas de la fila
    const cells = row.querySelectorAll('td');
    row.setAttribute('id', 'editRow');
    
    // Definir funciones para manejar cada tipo de campo
    const fieldHandlers = {
        'fechaInicio': (value) => {
            // Crear un campo de fecha con el valor actual y una fecha máxima igual a la fecha actual
            return `<input type="date" value="${value}" max="${getCurrentDate()}" required>`;
        },
        'fechaFin': (value) => {
            // Crear un campo de fecha con el valor actual y una fecha máxima igual a la fecha actual
            return `<input type="date" value="${value}" min="${getCurrentDate()}" required>`;
        },
        'nombre': (value) => {
            // Crear un campo de texto con el valor actual
            return `<input type="text" value="${value}" required>`;
        },
        'valor': (value) => {
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
            const value = (field === 'fechaInicio' || field === 'fechaFin') ? cell.dataset.iso : cell.innerText;
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
        <button onclick="updateImpuesto(${row.dataset.id})">Guardar</button>
        <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
    `;
}

/**
 * Muestra una fila para crear un nuevo impuesto en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo impuesto. La fila incluye botones para crear o cancelar.
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
        <td data-field="valor"><input type="number" min="0" step="0.01" required></td>
        <td data-field="descripcion"><input type="text"></td>
        <td data-field="fechaInicio"><input type="date" required max="${getCurrentDate()}"></td>
        <td data-field="fechaFin"><input type="date" required min="${getCurrentDate()}"></td>
        <td>
            <button onclick="createImpuesto()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
}

/**
 * Cancela la edición de un impuesto.
 * 
 * @description Esta función recarga los datos de impuestos para cancelar la edición actual.
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
        editRow.querySelector('[data-field="valor"]').innerHTML = originalData.valor;
        editRow.querySelector('[data-field="descripcion"]').innerHTML = originalData.descripcion;
        editRow.querySelector('[data-field="fechaInicio"]').innerHTML = originalData.fechaInicio;
        editRow.querySelector('[data-field="fechaFin"]').innerHTML = originalData.fechaFin;

        // Eliminar el atributo data-original-data
        delete editRow.dataset.originalData;

        // Restaurar los botones de la fila
        const cells = editRow.querySelectorAll('td');
        const lastCellIndex = cells.length - 1;
        cells[lastCellIndex].innerHTML = `
            <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
            <button onclick="deleteImpuesto(${editRow.dataset.id})">Eliminar</button>
        `;

        // Eliminar el atributo id de la fila de edición
        editRow.removeAttribute('id');
    }
}

/**
 * Cancela la creación de un nuevo impuesto.
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