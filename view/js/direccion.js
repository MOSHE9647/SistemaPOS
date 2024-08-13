// Función para hacer una fila editable
function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) { // Excluye la última columna (acciones)
        let value = cells[i].innerText;
        cells[i].innerHTML = `<input type="text" value="${value}" ${cells[i].dataset.field === 'estado' ? 'required' : ''}>`;
    }
    let actionCell = cells[cells.length - 1];
    actionCell.innerHTML = `
        <button onclick="saveRow(${row.dataset.id})">Guardar</button>
        <button onclick="cancelEdit()">Cancelar</button>`;
}

// Función para mostrar la fila de creación
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="provincia"><input type="text" required></td>
        <td data-field="canton"><input type="text" required></td>
        <td data-field="distrito"><input type="text" required></td>
        <td data-field="barrio"><input type="text" required></td>
        <td data-field="senas"><input type="text"></td>
        <td data-field="distancia"><input type="text"></td>
        <td data-field="estado"><input type="text" required></td>
        <td>
            <button onclick="createRow()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.appendChild(newRow);
}

// Función para crear una nueva dirección
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

    fetch('../controller/direccionAction.php', {
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
            location.reload();
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para guardar los cambios de una dirección
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

    fetch('../controller/direccionAction.php', {
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
            location.reload();
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para eliminar una dirección
function deleteRow(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta dirección?')) {
        return;
    }

    fetch('../controller/direccionAction.php', {
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
            document.querySelector(`tr[data-id='${id}']`).remove();
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para validar los campos de entrada
function validateInputs(inputs) {
    return Array.from(inputs).every(input => input.value.trim() !== '');
}

// Función para mostrar mensajes al usuario
function showMessage(message, type) {
    let messageDiv = document.getElementById('message');
    messageDiv.innerHTML = `<p class="${type}">${message}</p>`;
}

// Función para cancelar la edición de una fila
function cancelEdit() {
    location.reload();
}

// Función para cancelar la creación de una nueva dirección
function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block';
}

// Función para obtener la fecha actual en formato ISO
function getCurrentDate() {
    let today = new Date();
    return today.toISOString().split('T')[0];
}
let currentPage = 1;
let totalPages = 1;

function fetchDirecciones(page) {
    fetch(`../controller/direccionAction.php?page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Renderizar la tabla con los datos recibidos
                renderTable(data.listaDirecciones);
                currentPage = data.paginaActual;
                totalPages = data.totalPaginas;
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

function renderTable(direcciones) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    direcciones.forEach(direccion => {
        let row = `<tr data-id="${direccion.ID}">
            <td>${direccion.Provincia}</td>
            <td>${direccion.Canton}</td>
            <td>${direccion.Distrito}</td>
            <td>${direccion.Barrio}</td>
            <td>${direccion.Sennas}</td>
            <td>${direccion.Distancia}</td>
            <td>${direccion.Estado}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteRow(${direccion.ID})">Eliminar</button>
            </td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}

function updatePaginationControls() {
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

function changePage(newPage) {
    if (newPage >= 1 && newPage <= totalPages) {
        fetchDirecciones(newPage);
    }
}

// Llamada inicial para cargar la primera página
fetchDirecciones(1);

