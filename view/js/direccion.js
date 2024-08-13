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
