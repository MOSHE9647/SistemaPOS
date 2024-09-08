// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza una tabla con las relaciones Proveedor-Producto proporcionadas.
 * 
 * @param {Array} relaciones - Un arreglo de objetos de Proveedor-Producto.
 */
function renderTable(relaciones) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    relaciones.forEach(relacion => {
        let row = `
            <tr data-id="${relacion.ID}">
                <td data-field="proveedornombre">${relacion.ProveedorNombre}</td>
                <td data-field="productonombre">${relacion.ProductoNombre}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteProveedorProducto(${relacion.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });

    checkEmptyTable();
}

/**
 * Convierte una fila en editable.
 * 
 * @param {HTMLElement} row - La fila que se desea convertir en editable.
 */
function makeRowEditable(row) {
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        const field = cell.dataset.field;

        if (index < lastCellIndex) {
            if (field === 'proveedornombre') {
                cell.innerHTML = `<select id="proveedorid-select" required></select>`;
                loadOptions('proveedor', value);  // Cargar las opciones para el select de proveedor
            } else if (field === 'productonombre') {
                cell.innerHTML = `<select id="productoid-select" required></select>`;
                loadOptions('producto', value);  // Cargar las opciones para el select de producto
            } else {
                cell.innerHTML = `<input type="text" value="${value}" required>`;
            }
        } else {
            cell.innerHTML = `
                <button onclick="updateProveedorProducto(${row.dataset.id})">Guardar</button>
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
    const url = field === 'producto' ? '../controller/productoAction.php?accion=listarProductos' : '../controller/proveedorAction.php?accion=listarProveedores';
    const selectElement = document.getElementById(`${field}id-select`);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message);
            }
            
            const items = field === 'producto' ? data.listaProductos : data.listaProveedores;

            selectElement.innerHTML = items.map(item => `
                <option value="${item.id}" ${item.id == selectedValue ? 'selected' : ''}>
                    ${item.nombre}
                </option>
            `).join('');
        })
        .catch(error => {
            console.error('Error al cargar opciones:', error);
        });
}


/**
 * Muestra la fila para crear un nuevo registro Proveedor-Producto.
 */
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="proveedornombre">
            <select id="proveedorid-select" required></select>
        </td>
        <td data-field="productonombre">
            <select id="productoid-select" required></select>
        </td>
        <td>
            <button onclick="createProveedorProducto()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    tableBody.insertBefore(newRow, tableBody.firstChild);
    loadOptions('producto', null);
    loadOptions('proveedor', null);
}

/**
 * Muestra un mensaje al usuario.
 * 
 * @param {string} message - El texto del mensaje que se desea mostrar.
 * @param {string} type - El tipo de mensaje (error o success).
 */
function showMessage(message, type) {
    let container = document.getElementById('message');
    if (container != null) {
        container.innerHTML = message;
        container.classList.remove('error', 'success', 'fade-out');
        container.classList.add('message');
        container.classList.add(type);
        container.classList.add('fade-in');
        setTimeout(() => {
            container.classList.replace('fade-in', 'fade-out');
        }, 5000);
    } else {
        alert(message);
    }
}

/**
 * Cancela la edición de una relación Proveedor-Producto.
 */
function cancelEdit() {
    fetchProveedorProductos(currentPage, pageSize);
}

/**
 * Cancela la creación de una nueva relación Proveedor-Producto.
 */
function cancelCreate() {
    const createRow = document.getElementById('createRow');
    if (createRow) {
        createRow.remove();
    }
    document.getElementById('createButton').style.display = 'inline-block';
}
