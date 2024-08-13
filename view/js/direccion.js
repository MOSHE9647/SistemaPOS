let totalRecords = 5;
let currentPage = 1;
let totalPages = 1;
const defaultPageSize = 5; // Tamaño de página predeterminado
let pageSize = defaultPageSize; // Tamaño de página actual

function fetchDirecciones(page, size) {
    fetch(`../controller/direccionAction.php?page=${page}&size=${size}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaDirecciones);
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

function renderTable(direcciones) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    direcciones.forEach(direccion => {
        let row = `<tr data-id="${direccion.ID}">
            <td data-field="provincia">${direccion.Provincia}</td>
            <td data-field="canton">${direccion.Canton}</td>
            <td data-field="distrito">${direccion.Distrito}</td>
            <td data-field="barrio">${direccion.Barrio}</td>
            <td data-field="sennas">${direccion.Sennas}</td>
            <td data-field="distancia">${direccion.Distancia} km</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteRow(${direccion.ID})">Eliminar</button>
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
        fetchDirecciones(newPage, pageSize);
    }
}

// Llamada inicial para cargar la primera página
fetchDirecciones(currentPage, pageSize);

function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) {
        let value = cells[i].innerText;
        cells[i].innerHTML = `<input type="text" value="${value}">`;
    }
    let actionCell = cells[cells.length - 1];
    actionCell.innerHTML = `
        <button onclick="saveRow(${row.dataset.id})">Guardar</button>
        <button onclick="cancelEdit()">Cancelar</button>`;
}

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
        <td data-field="sennas"><input type="text"></td>
        <td data-field="distancia"><input type="text"></td>
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

        // Convertir 'Distancia' a double
        if (fieldName === 'distancia') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

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
            fetchDirecciones(currentPage, pageSize); // Recargar datos para reflejar la nueva dirección
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

        // Convertir 'Distancia' a double
        if (fieldName === 'distancia') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

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
            fetchDirecciones(currentPage, pageSize); // Recargar datos para reflejar los cambios
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
            fetchDirecciones(currentPage, pageSize); // Recargar datos para reflejar la eliminación
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

function validateInputs(inputs) {
    return Array.from(inputs).every(input => input.value.trim() !== '');
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

function cancelEdit() {
    fetchDirecciones(currentPage, pageSize); // Recargar datos para cancelar la edición
}

function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block';
}

// Función para cambiar el tamaño de página
function changePageSize(newSize) {
    pageSize = newSize;
    fetchDirecciones(currentPage, pageSize);
}

// Ejemplo de llamada para cambiar el tamaño de página (puedes agregar un selector para esto en tu HTML)
document.getElementById('pageSizeSelector').addEventListener('change', (event) => {
    changePageSize(event.target.value);
});