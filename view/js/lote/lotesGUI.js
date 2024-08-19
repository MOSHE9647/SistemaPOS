// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza una tabla con los lotes proporcionados.
 * 
 * @param {Array} lotes - Un arreglo de objetos de lote.
 * @example
 * const lotes = [
 *   { loteid: 1, lotecodigo: 'A001', productonombre: 'Producto 1', lotecantidad: 100, loteprecio: 50.00, proveedornombre: 'Proveedor 1', lotefechaingreso: '2024-01-01', lotefechavencimiento: '2024-12-31' },
 *   { loteid: 2, lotecodigo: 'A002', productonombre: 'Producto 2', lotecantidad: 200, loteprecio: 30.00, proveedornombre: 'Proveedor 2', lotefechaingreso: '2024-02-01', lotefechavencimiento: '2024-11-30' },
 * ];
 * renderTable(lotes);
 */
function renderTable(lotes) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    lotes.forEach(lote => {

        
        let row = `
            <tr data-id="${lote.ID}">
                <td data-field="codigo">${lote.Codigo}</td>
                <td data-field="producto">${lote.Producto}</td>
                <td data-field="cantidad">${lote.Cantidad}</td>
                <td data-field="precio">${lote.Precio}</td>
                <td data-field="proveedor">${lote.Proveedor}</td>
                <td data-field="fechaIngreso">${lote.FechaIngreso}</td>
                <td data-field="fechaVencimiento">${lote.FechaVencimiento}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteLote(${lote.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}



/**
 * Formatea una fecha para que solo incluya el componente de la fecha (sin horas).
 * 
 * @param {string} date - La fecha en formato de cadena.
 * @returns {string} La fecha formateada en formato 'YYYY-MM-DD'.
 */
function formatDate(date) {
    // Asumimos que la fecha viene en formato 'YYYY-MM-DDTHH:MM:SS' o similar y queremos solo 'YYYY-MM-DD'
    return date.split('T')[0];
}

/**
 * Convierte una fila en editable.
 * 
 * @param {HTMLElement} row - La fila que se desea convertir en editable.
 * @description Convierte los elementos de la fila en inputs para editar los valores, y agrega botones para guardar o cancelar los cambios.
 * @example
 * makeRowEditable(document.getElementById('fila1'));
 */
function makeRowEditable(row) {
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        const field = cell.dataset.field;

        if (index < lastCellIndex) {
            if (field === 'lotecantidad') {
                cell.innerHTML = `<input type="number" value="${parseInt(value)}" min="0" step="1" required>`;
            } else if (field === 'loteprecio') {
                cell.innerHTML = `<input type="number" value="${parseFloat(value).toFixed(2)}" min="0" step="0.01" required>`;
            } else if (field === 'fechaIngreso' || field === 'fechaVencimiento') {
                const formattedDate = formatDate(value);
                cell.innerHTML = `<input type="date" value="${formattedDate}" required>`;
            } else if (field === 'proveedor' || field === 'producto') {
                cell.innerHTML = `<select id="${field}-select" required></select>`;
                loadOptions(field, value);
            } else {
                cell.innerHTML = `<input type="text" value="${value}" required>`;
            }
        } else {
            cell.innerHTML = `
                <button onclick="updateLote(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit()">Cancelar</button>
            `;
        }
    });
}
/**
 * Carga las opciones para los comboboxes de producto y proveedor.
 * 
 * @param {string} field - El tipo de campo ('producto' o 'proveedor').
 * @param {string} selectedValue - El valor seleccionado actualmente.
 */
function loadOptions(field, selectedValue) {
    const url = field === 'producto' ? '../controller/productoAction.php' : '../controller/proveedorAction.php';
    //const selectElement = document.getElementById(`${field}-select`);
    const selectElement = document.getElementById(`${field}id-select`);
    fetch(url)
        .then(response => response.json()) // Lee la respuesta como JSON
        .then(data => {
            console.log('Respuesta cruda:', data); // Muestra la respuesta cruda

            // Extrae el array correcto basado en el campo
            const items = field === 'producto' ? data.listaProductos : data.listaProveedores;

            if (!Array.isArray(items)) {
                throw new Error('Datos recibidos no son un array');
            }

            selectElement.innerHTML = items.map(item => `
                <option value="${item.ID}" ${item.ID == selectedValue ? 'selected' : ''}>
                    ${item.Nombre}
                </option>
            `).join('');
        })
        .catch(error => {
            console.error('Error al cargar opciones:', error);
        });
}



/**
 * Muestra la fila para crear un nuevo lote.
 * 
 * @description Oculta el botón de crear y agrega una nueva fila a la tabla para ingresar los datos del lote.
 * @example
 * showCreateRow();
 */
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="lotecodigo"><input type="text" required></td>
        <td data-field="productonombre">
            <select id="productoid-select" required></select>
        </td>
        <td data-field="lotecantidad"><input type="number" min="0" step="1" required></td>
        <td data-field="loteprecio"><input type="number" min="0" step="0.01" required></td>
        <td data-field="proveedornombre">
            <select id="proveedorid-select" required></select>
        </td>
        <td data-field="lotefechaingreso"><input type="date" required></td>
        <td data-field="lotefechavencimiento"><input type="date" required></td>
        <td>
            <button onclick="createLote()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

   // Inserta la nueva fila al principio del cuerpo de la tabla
   tableBody.insertBefore(newRow, tableBody.firstChild);
      // Cargar opciones para los comboboxes
      loadOptions('producto', null);
      loadOptions('proveedor', null);
}


/**
 * Muestra un mensaje al usuario.
 * 
 * @param {string} message - El texto del mensaje que se desea mostrar.
 * @param {string} type - El tipo de mensaje (error o success).
 * @description Muestra un mensaje en la pantalla con el texto y tipo especificados, y lo oculta después de unos segundos.
 * @example
 * showMessage('Lote creado con éxito', 'success');
 */
function showMessage(message, type) {
    let container = document.getElementById('message');
    if (container != null) {
        container.innerHTML = message;
        
        // Primero eliminamos las clases relacionadas con mensajes anteriores
        container.classList.remove('error', 'success', 'fade-out');
        
        // Agregamos las clases apropiadas según el tipo
        container.classList.add('message');
        if (type === 'error') {
            container.classList.add('error');
        } else if (type === 'success') {
            container.classList.add('success');
        }

        container.classList.add('fade-in');
        
        // Oculta el mensaje después de unos segundos
        setTimeout(() => {
            container.classList.replace('fade-in', 'fade-out');
        }, 5000); // Tiempo durante el cual el mensaje es visible
    } else {
        alert(message);
    }
}

/**
 * Cancela la edición de un lote.
 * 
 * @description Recarga los datos de lotes para cancelar la edición en curso.
 * @example
 * cancelEdit();
 */
function cancelEdit() {
    fetchLotes(currentPage, pageSize); // Recargar datos para cancelar la edición
}

/**
 * Cancela la creación de un nuevo lote.
 * 
 * @description Elimina la fila de creación y muestra nuevamente el botón de crear.
 * @example
 * cancelCreate();
 */
function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block';
}
