// Función para hacer una fila editable
function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) { // Excluimos la última columna
        let value = cells[i].innerText;
        let fieldType = cells[i].dataset.field;

        // Si la columna es 'fecha_registro', usar un input de tipo date
        if (cells[i].dataset.field === 'fecha_registro') {
            value = cells[i].dataset.iso; // Obtener el valor en formato 'Y-m-d'
            cells[i].innerHTML = `<input type="date" value="${value}" max="${getCurrentDate()}">`;
        } else if (cells[i].dataset.field === 'nombre'  || fieldType === 'email') {
            cells[i].innerHTML = `<input type="text" value="${value}" required>`;
        } else {
            cells[i].innerHTML = `<input type="text" value="${value}">`;
        }
    }
    let actionCell = cells[cells.length - 1];
    actionCell.innerHTML = `<button onclick="saveRow(${row.dataset.id})">Guardar</button>
                            <button onclick="cancelEdit()">Cancelar</button>`;
}

// Función para mostrar la fila de creación
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none'; // Oculta el botón de crear

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

// Función para crear un nuevo proveedor
function createRow() {
    let row = document.getElementById('createRow');
    let inputs = row.querySelectorAll('input');
    let data = { accion: 'insertar' };

    // Validar campos obligatorios
    if (!validateInputs(inputs)) {
        localStorage.setItem('message', 'Por favor, complete todos los campos obligatorios.');
        localStorage.setItem('messageType', 'error');
        location.reload(); // Recargar la página para reflejar los cambios
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
            localStorage.setItem('message', data.message);
            localStorage.setItem('messageType', 'success');
            location.reload(); // Recargar la página para reflejar los cambios
        } else {
            localStorage.setItem('message', data.message);
            localStorage.setItem('messageType', 'error');
            location.reload(); // Recargar la página para reflejar los cambios
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para guardar los cambios de un proveedor
function saveRow(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input');
    let data = { accion: 'actualizar', id: id };

    // Validar campos obligatorios
    if (!validateInputs(inputs)) {
        localStorage.setItem('message', 'Por favor, complete todos los campos obligatorios.');
        localStorage.setItem('messageType', 'error');
        location.reload(); // Recargar la página para reflejar los cambios
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
            localStorage.setItem('message', data.message);
            localStorage.setItem('messageType', 'success');
            location.reload(); // Recargar la página para reflejar los cambios
        } else {
            localStorage.setItem('message', data.message);
            localStorage.setItem('messageType', 'error');
            location.reload(); // Recargar la página para reflejar los cambios
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para eliminar un proveedor
function deleteRow(id) {
    if (confirm('¿Está seguro de que desea eliminar este proveedor?')) {
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
                localStorage.setItem('message', data.message);
                localStorage.setItem('messageType', 'success');
                location.reload(); // Recargar la página para reflejar los cambios
            } else {
                localStorage.setItem('message', data.message);
                localStorage.setItem('messageType', 'error');
                location.reload(); // Recargar la página para reflejar los cambios
            }
        })
        .catch(error => {
            console.error('Error:', error);
           // showMessage('BIEN Ocurrió un error al procesar la solicitud.', 'error');
            location.reload(); // Recargar la página para reflejar los cambios
        });
    }
}

// Función para cancelar la edición
function cancelEdit() {
    location.reload(); // Recargar la página para cancelar la edición
}

// Función para cancelar la creación
function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block'; // Volver a mostrar el botón de crear
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
    } else {
        alert('Error al mostrar el mensaje');
    }
}

// Función para validar inputs obligatorios
function validateInputs(inputs) {
    let valid = true;
    inputs.forEach(input => {
        if (input.required && !input.value) {
            valid = false;
        }
    });
    return valid;
}

// Función para obtener la fecha actual en formato 'Y-MM-dd'
function getCurrentDate() {
    let today = new Date();
    let year = today.getFullYear();
    let month = (today.getMonth() + 1).toString().padStart(2, '0');
    let day = today.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Función para mostrar mensajes almacenados en localStorage
function displayStoredMessage() {
    let message = localStorage.getItem('message');
    let messageType = localStorage.getItem('messageType');
    if (message && messageType) {
        showMessage(message, messageType);
        localStorage.removeItem('message');
        localStorage.removeItem('messageType');
    }
}

// Ejecutar esta función cuando se cargue la página
document.addEventListener('DOMContentLoaded', displayStoredMessage);
