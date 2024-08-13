let totalRecords = 5;
let currentPage = 1;
let totalPages = 1;
const defaultPageSize = 5; // Tamaño de página predeterminado
let pageSize = defaultPageSize; // Tamaño de página actual

function fetchProveedores(page, size) {
    fetch(`../controller/proveedorAction.php?page=${page}&size=${size}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaProveedores);
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
            console.error('Error: ', error);
            showMessage('Ocurrió un error al procesar la solicitud.', 'error');
        });
}

function renderTable(proveedores) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    proveedores.forEach(proveedor => {
        let row = `<tr data-id="${proveedor.ID}">
            <td data-field="nombre">${proveedor.Nombre}</td>
            <td data-field="email">${proveedor.Email}</td>
            <td data-field="tipo">${proveedor.Tipo}</td>
            <td data-field="fecha_registro" data-iso="${proveedor.FechaISO}">${proveedor.Fecha}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteRow(${proveedor.ID})">Eliminar</button>
            </td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}

function updatePaginationControls() {
    document.getElementById('totalRecords').textContent = totalRecords;
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

function changePage(newPage) {
    if (newPage >= 1 && newPage <= totalPages) {
        fetchProveedores(newPage, pageSize);
    }
}

// Llamada inicial para cargar la primera página
fetchProveedores(currentPage, pageSize);

function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) {
        let value = cells[i].innerText;
        let fieldType = cells[i].dataset.field;

        // Si la columna es 'fecha_registro', usar un input de tipo date
        if (fieldType === 'fecha_registro') {
            value = cells[i].dataset.iso; // Obtener el valor en formato 'Y-m-d'
            cells[i].innerHTML = `<input type="date" value="${value}" max="${getCurrentDate()}">`;
        } else if (fieldType === 'nombre' || fieldType === 'email') {
            cells[i].innerHTML = `<input type="text" value="${value}" required>`;
        } else {
            cells[i].innerHTML = `<input type="text" value="${value}">`;
        }
    }
    let actionCell = cells[cells.length - 1];
    actionCell.innerHTML = `<button onclick="saveRow(${row.dataset.id})">Guardar</button>
                            <button onclick="cancelEdit()">Cancelar</button>`;
}

function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="nombre"><input type="text" required></td>
        <td data-field="email"><input type="text" required></td>
        <td data-field="tipo"><input type="text"></td>
        <td data-field="fecha_registro"><input type="date" required max="${getCurrentDate()}"></td>
        <td>
            <button onclick="createRow()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.appendChild(newRow);
}

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

    fetch('../controller/proveedorAction.php', {
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
            fetchProveedores(currentPage, pageSize); // Recargar datos para reflejar el nuevo proveedor
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

function saveRow(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input');
    let data = { accion: 'actualizar', id: id };

    if (!validateInputs(inputs)) {
        showMessage('Por favor, complete todos los campos obligatorios.', 'error');
        return;
    }

    inputs.forEach(input => {
        let fieldName = input.closest('td').dataset.field;
        let value = input.value;
        data[fieldName] = value;
    });

    fetch('../controller/proveedorAction.php', {
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
            fetchProveedores(currentPage, pageSize); // Recargar datos para reflejar los cambios
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

function deleteRow(id) {
    if (!confirm('¿Está seguro de que desea eliminar este proveedor?')) {
        return;
    }

    fetch('../controller/proveedorAction.php', {
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
            fetchProveedores(currentPage, pageSize); // Recargar datos para reflejar la eliminación
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

function validateInputs(inputs) {
    return Array.from(inputs).every(input => input.value.trim() !== '');
}

// Función para cancelar la edición
function cancelEdit(id) {
    fetchProveedores(currentPage, pageSize); // Recargar la página para cancelar la edición
}

// Función para cancelar la creación
function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block'; // Volver a mostrar el botón de crear
}

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

function getCurrentDate() {
    let today = new Date();
    let year = today.getFullYear();
    let month = (today.getMonth() + 1).toString().padStart(2, '0');
    let day = today.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}

document.getElementById('pageSizeSelector').addEventListener('change', (event) => {
    changePageSize(parseInt(event.target.value));
});

function changePageSize(newSize) {
    pageSize = newSize;
    fetchProveedores(1, pageSize); // Volver a la primera página con el nuevo tamaño de página
}

document.getElementById('prevPage').addEventListener('click', () => {
    changePage(currentPage - 1);
});

document.getElementById('nextPage').addEventListener('click', () => {
    changePage(currentPage + 1);
});
