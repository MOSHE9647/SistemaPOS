// Función para hacer una fila editable
function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) { // Excluimos la última columna
        let value = cells[i].innerText;
        
        // Si la columna es 'fecha', usar un input de tipo date
        if (cells[i].dataset.field === 'fecha_vigencia') {
            value = cells[i].dataset.iso; // Obtener el valor en formato 'Y-MM-dd'
            cells[i].innerHTML = `<input type="date" value="${value}" max="${getCurrentDate()}">`;
        } else if (cells[i].dataset.field === 'nombre' || cells[i].dataset.field === 'valor') {
            cells[i].innerHTML = `<input type="text" value="${value}" required>`;
        } else {
            cells[i].innerHTML = `<input type="text" value="${value}">`;
        }
    }
    let actionCell = cells[cells.length - 1];
    actionCell.innerHTML = `<button onclick="saveRow(${row.dataset.id})">Guardar</button>
                            <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>`;
}

// Función para mostrar la fila de creación
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none'; // Oculta el botón de crear

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="nombre"><input type="text" required></td>
        <td data-field="valor"><input type="text" required></td>
        <td data-field="descripcion"><input type="text"></td>
        <td data-field="fecha_vigencia"><input type="date" required max="${getCurrentDate()}"></td>
        <td>
            <button onclick="createRow()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.appendChild(newRow);
}

// Función para crear un nuevo impuesto
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

        // Convertir 'Valor' a double
        if (fieldName === 'valor') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

        data[fieldName] = value;
    });

    fetch('../controller/impuestoAction.php', {
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

// Función para guardar los cambios de un impuesto
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

        // Convertir 'Valor' a double
        if (fieldName === 'valor') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

        data[fieldName] = value;
    });

    fetch('../controller/impuestoAction.php', {
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

// Función para eliminar un impuesto
function deleteRow(id) {
    if (confirm('¿Está seguro de que desea eliminar este impuesto?')) {
        // alert('Datos a enviar:\n' + JSON.stringify({ accion: 'eliminar', id: id }, null, 2));

        fetch('../controller/impuestoAction.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'eliminar', id: id }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {    
                // Guardar el mensaje en localStorage
                localStorage.setItem('message', data.message);
                localStorage.setItem('messageType', 'success');
                location.reload(); // Recargar la página para reflejar los cambios
            } else {
                // Guardar el mensaje en localStorage
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

function displayStoredMessage() {
    let message = localStorage.getItem('message');
    let type = localStorage.getItem('messageType');

    if (message && type) {
        showMessage(message, type);
        // Limpiar el mensaje después de mostrarlo
        localStorage.removeItem('message');
        localStorage.removeItem('messageType');
    }
}

// Llama a displayStoredMessage al cargar la página
window.onload = displayStoredMessage;