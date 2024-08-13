// Función para hacer una fila editable
function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) { // Excluimos la última columna
        let value = cells[i].innerText;
        
        // Si la columna es 'fecha', usar un input de tipo date
        if (cells[i].dataset.field === 'fechaadquisicionproducto') {
            value = cells[i].dataset.iso; // Obtener el valor en formato 'Y-MM-dd'
            cells[i].innerHTML = `<input type="date" value="${value}" max="${getCurrentDate()}">`;
        } else if (cells[i].dataset.field === 'preciounitarioproducto' || cells[i].dataset.field === 'cantidadproducto') {
            cells[i].innerHTML = `<input type="number" value="${value}" required>`;
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
        <td data-field="nombreproducto"><input type="text" required></td>
        <td data-field="preciounitarioproducto"><input type="number" required></td>
        <td data-field="cantidadproducto"><input type="number" required></td>
        <td data-field="fechaadquisicionproducto"><input type="date" required max="${getCurrentDate()}"></td>
        <td data-field="descripcionproducto"><input type="text"></td>
        <td data-field="estadoproducto"><input type="text" required></td>
        <td>
            <button onclick="createRow()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.appendChild(newRow);
}

// Función para crear un nuevo producto
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

        // Convertir 'Precio Unitario' a double
        if (fieldName === 'preciounitarioproducto') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

        data[fieldName] = value;
    });

    fetch('../controller/productoAction.php', {
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

// Función para guardar los cambios de un producto
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

        // Convertir 'Precio Unitario' a double
        if (fieldName === 'preciounitarioproducto') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

        data[fieldName] = value;
    });

    fetch('../controller/productoAction.php', {
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

// Función para cancelar la edición de un producto
function cancelEdit() {
    location.reload(); // Recargar la página para cancelar la edición
}

// Función para cancelar la creación de un nuevo producto
function cancelCreate() {
    let row = document.getElementById('createRow');
    row.remove();
    document.getElementById('createButton').style.display = ''; // Mostrar el botón de crear
}

// Función para obtener la fecha actual en formato 'YYYY-MM-DD'
function getCurrentDate() {
    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0'); // Enero es 0!
    let yyyy = today.getFullYear();

    return yyyy + '-' + mm + '-' + dd;
}

// Función para validar que los campos obligatorios no estén vacíos
function validateInputs(inputs) {
    for (let input of inputs) {
        if (input.hasAttribute('required') && !input.value.trim()) {
            return false;
        }
    }
    return true;
}

// Función para eliminar un producto
function deleteRow(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        fetch('../controller/productoAction.php', {
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
            showMessage('Ocurrió un error al procesar la solicitud.', 'error');
        });
    }
}

// Función para mostrar mensajes en pantalla
function showMessage(message, type) {
    let messageDiv = document.getElementById('message');
    messageDiv.innerText = message;
    messageDiv.className = type;
}
