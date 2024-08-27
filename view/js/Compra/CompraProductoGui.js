// ************************************************************ //
// ********** Métodos para el manejo de la GUI de CompraProducto ********* //
// ************************************************************ //

/**
 * Renderiza la tabla de compras de productos con los datos proporcionados.
 * 
 * @param {Array} compras - Una lista de objetos que representan las compras de productos.
 * @example
 * const compras = [
 *   { compraProductoID: 1, compraProductoCantidad: 10, proveedorNombre: 'Proveedor 1', compraProductoFechaCreacion: '2024-01-01' },
 *   { compraProductoID: 2, compraProductoCantidad: 20, proveedorNombre: 'Proveedor 2', compraProductoFechaCreacion: '2024-01-02' },
 * ];
 * renderTable(compras);
 */
function renderTable(compras) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpiar la tabla

    compras.forEach(compra => {
        let row = `
            <tr data-id="${compra.compraProductoID}">
                <td data-field="compraproductoid">${compra.compraProductoID}</td>
                <td data-field="compraproductocantidad">${compra.compraProductoCantidad}</td>
                <td data-field="proveedornombre">${compra.proveedorNombre}</td>
                <td data-field="compraproductofechacreacion">${compra.compraProductoFechaCreacion}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteCompraProducto(${compra.compraProductoID})">Eliminar</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}

/**
 * Hace que una fila específica sea editable.
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
            if (field === 'compraproductocantidad') {
                cell.innerHTML = `<input type="number" value="${parseInt(value)}" min="1" step="1" required>`;
            } else if (field === 'proveedornombre') {
                cell.innerHTML = `<select id="proveedorid-select-${row.dataset.id}" required></select>`;
                loadProveedoresOptions(value, `proveedorid-select-${row.dataset.id}`);  // Cargar las opciones para el select de proveedor
            } else {
                cell.innerHTML = `<input type="text" value="${value}" readonly>`;
            }
        } else {
            cell.innerHTML = `
                <button onclick="updateCompraProducto(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit()">Cancelar</button>
            `;
        }
    });
}

/**
 * Muestra la fila para crear una nueva compra de producto.
 */
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="compraproductoid">Auto-generado</td>
        <td data-field="compraproductocantidad"><input type="number" min="1" step="1" required></td>
        <td data-field="proveedornombre">
            <select id="proveedorid-select" required></select>
        </td>
        <td data-field="compraproductofechacreacion">Auto-generado</td>
        <td>
            <button onclick="createCompraProducto()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    tableBody.insertBefore(newRow, tableBody.firstChild);

    // Cargar opciones para el select de proveedores
    loadProveedoresOptions(null, 'proveedorid-select');
}

/**
 * Carga las opciones para el select de proveedores.
 * 
 * @param {string} selectedValue - El valor seleccionado actualmente.
 * @param {string} selectId - El id del elemento select que se desea llenar.
 */
function loadProveedoresOptions(selectedValue, selectId) {
    fetch('../controller/CompraProductoAction.php?accion=listarProveedores')
        .then(response => response.json())
        .then(data => {
            const selectElement = document.getElementById(selectId);
            selectElement.innerHTML = '';

            if (data.success && Array.isArray(data.data)) {
                data.data.forEach(proveedor => {
                    let option = document.createElement('option');
                    option.value = proveedor.id;
                    option.text = proveedor.nombre;

                    if (proveedor.nombre === selectedValue) {
                        option.selected = true;
                    }
                    selectElement.appendChild(option);
                });
            } else {
                showMessage('No se pudieron cargar los proveedores.', 'error');
            }
        })
        .catch(error => {
            showMessage(`Error al cargar proveedores: ${error}`, 'error');
        });
}

/**
 * Muestra un mensaje al usuario.
 * 
 * @param {string} message - El mensaje que se mostrará.
 * @param {string} type - El tipo de mensaje ('success' o 'error').
 */
function showMessage(message, type) {
    let container = document.getElementById('message');
    if (container != null) {
        container.innerHTML = message;
        
        // Eliminar clases anteriores
        container.classList.remove('error', 'success', 'fade-out');
        
        // Agregar clase según el tipo de mensaje
        container.classList.add('message', type);
        container.classList.add('fade-in');
        
        // Ocultar el mensaje después de unos segundos
        setTimeout(() => {
            container.classList.replace('fade-in', 'fade-out');
        }, 5000); // Tiempo en milisegundos
    } else {
        alert(message);
    }
}

/**
 * Cancela la edición de una fila y recarga los datos originales.
 */
function cancelEdit() {
    fetchCompraProductos(currentPage, pageSize); // Recargar datos para cancelar la edición
}

/**
 * Cancela la creación de una nueva compra de producto.
 */
function cancelCreate() {
    const createRow = document.getElementById('createRow');
    if (createRow) {
        createRow.remove();
    }
    document.getElementById('createButton').style.display = 'inline-block';
}

/**
 * Crea una nueva compra de producto.
 * 
 * @description Envía una solicitud POST al servidor para crear una nueva compra de producto con los datos ingresados en la fila de creación.
 */
function createCompraProducto() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Obtener el ID del proveedor
        if (fieldName === 'proveedornombre') {
            value = document.getElementById('proveedorid-select').value;
        }

        data[fieldName] = value;
    });

    fetch('../controller/CompraProductoAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCompraProductos(currentPage, pageSize);  // Recargar datos para reflejar la nueva compra
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al crear la compra.<br>${error}`, 'error');
    });
}

/**
 * Actualiza una compra de producto existente.
 * 
 * @param {number} id - El ID de la compra de producto que se desea actualizar.
 */
function updateCompraProducto(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Obtener el ID del proveedor
        if (fieldName === 'proveedornombre') {
            value = document.getElementById(`proveedorid-select-${id}`).value;
        }

        data[fieldName] = value;
    });

    fetch('../controller/CompraProductoAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchCompraProductos(currentPage, pageSize);  // Recargar datos para reflejar la actualización
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage(`Ocurrió un error al actualizar la compra.<br>${error}`, 'error');
    });
}

/**
 * Elimina una compra de producto existente.
 * 
 * @param {number} id - El ID de la compra de producto que se desea eliminar.
 */
function deleteCompraProducto(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta compra de producto?')) {
        fetch('../controller/CompraProductoAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                fetchCompraProductos(currentPage, pageSize); // Recargar datos para reflejar la eliminación
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage(`Ocurrió un error al eliminar la compra.<br>${error}`, 'error');
        });
    }
}
