let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
let pageSize = 5; // Tamaño de página predeterminado

// Función para obtener los impuestos con paginación
function fetchImpuestos(page, size) {
    fetch(`../controller/impuestoAction.php?page=${page}&size=${size}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaImpuestos);
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
function renderTable(impuestos) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    impuestos.forEach(impuesto => {
        let row = `<tr data-id="${impuesto.ID}">
            <td data-field="nombre">${impuesto.Nombre}</td>
            <td data-field="valor">${impuesto.Valor}%</td>
            <td data-field="descripcion">${impuesto.Descripcion}</td>
            <td data-field="fecha_vigencia" data-iso="${impuesto.FechaVigenciaISO}">${impuesto.FechaVigencia}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteRow(${impuesto.ID})">Eliminar</button>
            </td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}

// Función para actualizar los controles de paginación
function updatePaginationControls() {
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

// Función para cambiar de página
function changePage(newPage) {
    if (newPage >= 1 && newPage <= totalPages) {
        fetchImpuestos(newPage, pageSize);
    }
}

// Función para cambiar el tamaño de página
function changePageSize(newSize) {
    pageSize = newSize;
    fetchImpuestos(currentPage, pageSize);
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
        showMessage('Por favor, complete todos los campos obligatorios.', 'error');
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
            showMessage(data.message, 'success');
            fetchImpuestos(currentPage, pageSize);
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

// Función para guardar los cambios de un impuesto
function saveRow(id) {
    let row = document.querySelector(`tr[data-id='${id}']`);
    let inputs = row.querySelectorAll('input');
    let data = { accion: 'actualizar', id: id };

    // Validar campos obligatorios
    if (!validateInputs(inputs)) {
        showMessage('Por favor, complete todos los campos obligatorios.', 'error');
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
            showMessage(data.message, 'success');
            fetchImpuestos(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
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
                showMessage(data.message, 'success');
                fetchImpuestos(currentPage, pageSize);
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
function cancelEdit(id) {
    fetchImpuestos(currentPage, pageSize); // Recargar la página para cancelar la edición
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

// Función para mostrar el mensaje almacenado
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
window.onload = function() {
    displayStoredMessage();
    fetchImpuestos(currentPage, pageSize); // Cargar los impuestos cuando se carga la página
}

// Controladores de eventos para la paginación
document.getElementById('prevPage').onclick = () => changePage(currentPage - 1);
document.getElementById('nextPage').onclick = () => changePage(currentPage + 1);

// Controlador de eventos para cambiar el tamaño de la página
document.getElementById('pageSize').onchange = (event) => changePageSize(parseInt(event.target.value, 10));
