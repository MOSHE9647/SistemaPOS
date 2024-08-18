let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
let pageSize = 5; // Tamaño de página predeterminado

// Función para obtener los lotes con paginación
function fetchLotes(page, size) {
    fetch(`../controller/loteAction.php?page=${page}&size=${size}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaLotes);
                currentPage = data.page;
                totalPages = data.totalPages;
                totalRecords = data.totalRecords;
                pageSize = data.size;
                updatePaginationControls();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Ocurrió un error al procesar la solicitud.', 'error');
        });
}

// Función para renderizar la tabla
function renderTable(lotes) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    lotes.forEach(lote => {
        let row = `<tr data-id="${lote.ID}">
            <td data-field="codigo">${lote.Codigo}</td>
            <td data-field="producto">${lote.ProductoID}</td>
            <td data-field="cantidad">${lote.Cantidad}</td>
            <td data-field="precio">${lote.Precio}</td>
            <td data-field="proveedor">${lote.ProveedorID}</td>
            <td data-field="fecha_ingreso">${lote.FechaIngreso}</td>
            <td data-field="fecha_vencimiento">${lote.FechaVencimiento}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteRow(${lote.ID})">Eliminar</button>
            </td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}

// Función para actualizar los controles de paginación
function updatePaginationControls() {
    document.getElementById('totalRecords').textContent = totalRecords;
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

// Función para cambiar de página
function changePage(newPage) {
    if (newPage >= 1 && newPage <= totalPages) {
        fetchLotes(newPage, pageSize);
    }
}

// Función para cambiar el tamaño de página
function changePageSize(newSize) {
    pageSize = newSize;
    fetchLotes(currentPage, pageSize);
}

// Función para hacer una fila editable
function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) { // Excluimos la última columna
        let value = cells[i].innerText;

        // Si la columna es 'fecha', usar un input de tipo date
        if (cells[i].dataset.field === 'fecha_ingreso' || cells[i].dataset.field === 'fecha_vencimiento' || cells[i].dataset.field === 'fecha_creacion') {
            value = cells[i].dataset.iso; // Obtener el valor en formato 'YYYY-MM-DD'
            cells[i].innerHTML = `<input type="date" value="${value}" max="${getCurrentDate()}" required>`;
        } else if (cells[i].dataset.field === 'cantidad') {
            cells[i].innerHTML = `<input type="number" value="${value}" min="0" step="1" required>`; // 'cantidad' es entero
        } else if (cells[i].dataset.field === 'precio') {
            cells[i].innerHTML = `<input type="number" value="${value}" min="0" step="0.01" required>`; // 'precio' es decimal
        } else if (cells[i].dataset.field === 'proveedor' || cells[i].dataset.field === 'producto') {
            cells[i].innerHTML = `<input type="number" value="${value}" min="0" required>`; // 'proveedor' y 'producto' son enteros
        } else {
            cells[i].innerHTML = `<input type="text" value="${value}">`;
        }
    }
    let actionCell = cells[cells.length - 1];
    actionCell.innerHTML = `<button onclick="saveRow(${row.dataset.id})">Guardar</button>
                            <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>`;
}


// Función para convertir fechas a formato ISO
function formatDateToISO(dateStr) {
    // Supón que `dateStr` está en formato 'dd/MM/yyyy' o similar, ajusta según el formato real
    let [day, month, year] = dateStr.split('/').map(num => num.padStart(2, '0')); 
    return `${year}-${month}-${day}`;
}

// Función para obtener la fecha actual
function getCurrentDate() {
    let today = new Date();
    let day = today.getDate().toString().padStart(2, '0');
    let month = (today.getMonth() + 1).toString().padStart(2, '0');
    let year = today.getFullYear();
    return `${year}-${month}-${day}`;
}


// Función para mostrar la fila de creación
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none'; // Oculta el botón de crear

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="codigo"><input type="text" required></td>
        <td data-field="producto"><input type="text" required></td>
        <td data-field="cantidad"><input type="number" min="0" step="0.01" required></td>
        <td data-field="precio"><input type="number" min="0" step="0.01" required></td>
        <td data-field="proveedor"><input type="text" required></td>
        <td data-field="fecha_ingreso"><input type="date" required max="${getCurrentDate()}"></td>
        <td data-field="fecha_vencimiento"><input type="date" required max="${getCurrentDate()}"></td>
        <td>
            <button onclick="createRow()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.appendChild(newRow);
}

// Función para crear un nuevo lote
function createRow() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'insertar' };

    // Validar campos obligatorios
    if (!validateInputs(inputs)) {
        showMessage('Por favor, complete todos los campos obligatorios.', 'error');
        return;
    }

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Convertir 'Cantidad' y 'Precio' a double
        if (fieldName === 'cantidad' || fieldName === 'precio') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

        data[fieldName] = value;
    });

    fetch('../controller/loteAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchLotes(currentPage, pageSize);
            document.getElementById('createRow').remove();
            document.getElementById('createButton').style.display = 'inline-block';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para guardar los cambios de un lote
function saveRow(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input, select');
    let data = { accion: 'actualizar', id: id };

    // Validar campos obligatorios
    if (!validateInputs(inputs)) {
        showMessage('Por favor, complete todos los campos obligatorios.', 'error');
        return;
    }

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;

        // Convertir 'Cantidad' y 'Precio' a double
        if (fieldName === 'cantidad' || fieldName === 'precio') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

        data[fieldName] = value;
    });

    fetch('../controller/loteAction.php', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            fetchLotes(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para eliminar un lote
function deleteRow(id) {
    if (confirm('¿Está seguro de que desea eliminar este lote?')) {
        fetch('../controller/loteAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                fetchLotes(currentPage, pageSize);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Ocurrió un error al procesar la solicitud.', 'error');
        });
    }
}

// Función para cancelar la edición de una fila
function cancelEdit(id) {
    fetchLotes(currentPage, pageSize);
}

// Función para cancelar la creación de un nuevo lote
function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block';
}

// Función para validar inputs
function validateInputs(inputs) {
    return Array.from(inputs).every(input => input.value.trim() !== '');
}

// Función para obtener la fecha actual
function getCurrentDate() {
    let today = new Date();
    let day = today.getDate().toString().padStart(2, '0');
    let month = (today.getMonth() + 1).toString().padStart(2, '0');
    let year = today.getFullYear();
    return `${year}-${month}-${day}`;
}

// Inicialización
document.getElementById('prevPage').addEventListener('click', () => changePage(currentPage - 1));
document.getElementById('nextPage').addEventListener('click', () => changePage(currentPage + 1));
document.getElementById('pageSizeSelector').addEventListener('change', (event) => changePageSize(parseInt(event.target.value)));
document.getElementById('createButton').addEventListener('click', showCreateRow);

// Cargar la primera página
fetchLotes(currentPage, pageSize);
