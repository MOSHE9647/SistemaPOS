let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
let pageSize = 5; // Tamaño de página predeterminado

// Función para obtener las categorías con paginación
function fetchCategorias(page, size) {
    fetch(`../controller/categoriaAction.php?page=${page}&size=${size}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaCategorias);
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
function renderTable(categorias) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    categorias.forEach(categoria => {
        let row = `<tr data-id="${categoria.ID}">
            <td data-field="nombre">${categoria.Nombre}</td>
            <td data-field="estado">${categoria.Estado}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteRow(${categoria.ID})">Eliminar</button>
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
        fetchCategorias(newPage, pageSize);
    }
}

// Función para cambiar el tamaño de página
function changePageSize(newSize) {
    pageSize = newSize;
    fetchCategorias(currentPage, pageSize);
}

// Función para hacer una fila editable
function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) { // Excluimos la última columna
        let value = cells[i].innerText;
        if (cells[i].dataset.field === 'nombre' || cells[i].dataset.field === 'estado') {
            cells[i].innerHTML = `<input type="text" value="${value}" required>`;
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
        <td data-field="estado"><input type="text" required></td>
        <td>
            <button onclick="createRow()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.appendChild(newRow);
}

// Función para crear una nueva categoría
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
        data[fieldName] = input.value;
    });

    fetch('../controller/categoriaAction.php', {
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
            fetchCategorias(currentPage, pageSize);
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

// Función para guardar los cambios de una categoría
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
        data[fieldName] = input.value;
    });

    fetch('../controller/categoriaAction.php', {
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
            fetchCategorias(currentPage, pageSize);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Ocurrió un error al procesar la solicitud.', 'error');
    });
}

// Función para eliminar una categoría
function deleteRow(id) {
    if (confirm('¿Está seguro de que desea eliminar esta categoría?')) {
        fetch('../controller/categoriaAction.php', {
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
                fetchCategorias(currentPage, pageSize);
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
    fetchCategorias(currentPage, pageSize); // Recargar la página para cancelar la edición
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
    fetchCategorias(currentPage, pageSize); // Cargar las categorías cuando se carga la página
}

// Controladores de eventos para la paginación
document.getElementById('prevPage').onclick = () => changePage(currentPage - 1);
document.getElementById('nextPage').onclick = () => changePage(currentPage + 1);

// Controlador de eventos para cambiar el tamaño de página
document.getElementById('pageSize').onchange = (event) => changePageSize(event.target.value);
