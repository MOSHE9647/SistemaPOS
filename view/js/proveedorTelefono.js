let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
const defaultPageSize = 5;
let pageSize = defaultPageSize;

// Función para obtener proveedores telefónicos
function fetchProveedorTelefonos(page, size) {
    fetch(`../controller/proveedorTelefonoAction.php?page=${page}&size=${size}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaTelefonos);
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

// Función para renderizar la tabla de proveedores telefónicos
function renderTable(proveedorTelefonos) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    proveedorTelefonos.forEach(proveedorTelefono => {
        let row = `<tr data-id="${proveedorTelefono.ID}">
            <td data-field="proveedorid">${proveedorTelefono.ProveedorID}</td>
            <td data-field="proveedor">${proveedorTelefono.Proveedor}</td>
            <td data-field="telefono">${proveedorTelefono.Telefono}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteRow(${proveedorTelefono.ID})">Eliminar</button>
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

// Función para cambiar la página
function changePage(newPage) {
    if (newPage >= 1 && newPage <= totalPages) {
        fetchProveedorTelefonos(newPage, pageSize);
    }
}

// Función para cambiar el tamaño de la página
function changePageSize(newSize) {
    pageSize = newSize;
    fetchProveedorTelefonos(currentPage, pageSize);
}

// Llamada inicial para cargar la primera página
fetchProveedorTelefonos(currentPage, pageSize);

// Función para hacer una fila editable
function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) {
        let value = cells[i].innerText;
        
        if (cells[i].dataset.field === 'proveedor') {
            cells[i].innerHTML = `<input type="text" value="${value}" disabled>`;
        } else if(cells[i].dataset.field === 'proveedorid') {
            cells[i].innerHTML = `<input type="number" min=0 value="${value}" required>`;
        } else {
            cells[i].innerHTML = `<input type="text" value="${value}" required>`;
        }
    }
    let actionCell = cells[cells.length - 1];
    actionCell.innerHTML = `<button onclick="saveRow(${row.dataset.id})">Guardar</button>
                            <button onclick="cancelEdit()">Cancelar</button>`;
}

// Función para mostrar la fila de creación
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="proveedorid"><input type="number" min=0 required></td>
        <td data-field="proveedor"><input class="proveedor" type="text" disabled></td>
        <td data-field="telefono"><input type="text" required></td>
        <td>
            <button onclick="createRow()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.appendChild(newRow);
}

// Función para crear un nuevo registro de ProveedorTelefono
function createRow() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input');
    let data = { accion: 'insertar' };

    if (!validateInputs(inputs)) {
        showMessage('Por favor, complete todos los campos obligatorios.', 'error');
        return;
    }

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;
        data[fieldName] = value;
    });

    fetch('../controller/proveedorTelefonoAction.php', {
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
            fetchProveedorTelefonos(currentPage, pageSize);
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

// Función para guardar los cambios de un registro de ProveedorTelefono
function saveRow(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input');
    let data = { accion: 'actualizar', proveedortelefonoid: id };

    if (!validateInputs(inputs)) {
        showMessage('Por favor, complete todos los campos obligatorios.', 'error');
        return;
    }

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;
        data[fieldName] = value;
    });

    fetch('../controller/proveedorTelefonoAction.php', {
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
            fetchProveedorTelefonos(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para eliminar un registro de ProveedorTelefono
function deleteRow(id) {
    if (confirm('¿Está seguro de que desea eliminar este registro?')) {
        fetch('../controller/proveedorTelefonoAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', proveedortelefonoid: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                fetchProveedorTelefonos(currentPage, pageSize);
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

// Función para cancelar la edición
function cancelEdit() {
    fetchProveedorTelefonos(currentPage, pageSize);
}

// Función para cancelar la creación
function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block';
}

// Función para mostrar mensajes
function showMessage(message, type) {
    let container = document.getElementById('message');
    if (container != null) {
        container.innerHTML = message;
        
        // Primero eliminamos las clases relacionadas con mensajes anteriores
        container.classList.remove('error', 'success');
        
        // Agregamos las clases apropiadas según el tipo
        container.classList.add('message');
        if (type === 'error') {
            container.classList.add('error');
        } else if (type === 'success') {
            container.classList.add('success');
        }

        container.style.display = 'flex';
    } else {
        alert('Error al mostrar el mensaje');
    }
}

// Función para validar inputs obligatorios
function validateInputs(inputs) {
    // Filtra los inputs para excluir aquellos con la clase 'proveedor'
    const filteredInputs = Array.from(inputs).filter(input => !input.classList.contains('proveedor'));
    
    // Verifica que todos los inputs filtrados tengan un valor no vacío
    return filteredInputs.every(input => input.value.trim() !== '');
}

// Función para mostrar mensajes almacenados
function displayStoredMessage() {
    let message = localStorage.getItem('message');
    let type = localStorage.getItem('messageType');

    if (message && type) {
        showMessage(message, type);
        localStorage.removeItem('message');
        localStorage.removeItem('messageType');
    }
}

// Eventos de paginación
document.getElementById('prevPage').addEventListener('click', () => changePage(currentPage - 1));
document.getElementById('nextPage').addEventListener('click', () => changePage(currentPage + 1));
document.getElementById('pageSizeSelector').addEventListener('change', (event) => changePageSize(event.target.value));

// Llama a displayStoredMessage al cargar la página
window.onload = displayStoredMessage;
