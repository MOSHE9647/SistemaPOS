let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;
const defaultPageSize = 5;
let pageSize = defaultPageSize;

function fetchSubcategorias(page, size) {
    fetch(`../controller/subcategoriaAction.php?page=${page}&size=${size}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.listaSubcategorias);
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

function renderTable(subcategorias) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';
    subcategorias.forEach(subcategoria => {
        let row = `<tr data-id="${subcategoria.ID}">
            <td data-field="nombre">${subcategoria.Nombre}</td>
            <td data-field="descripcion">${subcategoria.Descripcion}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteRow(${subcategoria.ID})">Eliminar</button>
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
        fetchSubcategorias(newPage, pageSize);
    }
}

function changePageSize(newSize) {
    pageSize = newSize;
    fetchSubcategorias(currentPage, pageSize);
}

document.getElementById('pageSizeSelector').addEventListener('change', (event) => {
    changePageSize(event.target.value);
});

// Llamada inicial para cargar la primera página
fetchSubcategorias(currentPage, pageSize);

function makeRowEditable(row) {
    let cells = row.querySelectorAll('td');
    for (let i = 0; i < cells.length - 1; i++) {
        let value = cells[i].innerText;
        let fieldName = cells[i].dataset.field;

        // Si la columna es 'fecha', usar un input de tipo date
        if (fieldName === 'fecha') {
            value = cells[i].dataset.iso; // Obtener el valor en formato 'Y-MM-dd'
            cells[i].innerHTML = `<input type="date" value="${value}" max="${getCurrentDate()}">`;
        } else if (fieldName === 'precio' || fieldName === 'cantidad') {
            cells[i].innerHTML = `<input type="number" value="${value}" required>`;
        } else {
            cells[i].innerHTML = `<input type="text" value="${value}">`;
        }
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
        <td data-field="nombre"><input type="text" required></td>
        <td data-field="descripcion"><input type="text"></td>
        <td>
            <button onclick="createRow()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.insertBefore(newRow, tableBody.firstChild);
    // tableBody.appendChild(newRow);
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

        // Convertir 'Precio Unitario' y 'Cantidad' a double
        if (fieldName === 'precio') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }

        data[fieldName] = value;
    });
    console.log(data);
    fetch('../controller/subcategoriaAction.php', {
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
            fetchSubcategorias(currentPage, pageSize); // Recargar datos para reflejar el nuevo producto
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

        // Convertir 'Precio Unitario' y 'Cantidad' a double
        if (fieldName === 'precio') {
            value = parseFloat(value).toFixed(2); // Convertir a double y limitar a 2 decimales
        }
   
        data[fieldName] = value;
    });
    console.log(data);
    fetch('../controller/subcategoriaAction.php', {
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
            fetchSubcategorias(currentPage, pageSize); // Recargar datos para reflejar los cambios
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
    if (!confirm('¿Estás seguro de que deseas eliminar esta subcategoria?')) {
        return;
    }

    fetch('../controller/subcategoriaAction.php', {
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
            fetchSubcategorias(currentPage, pageSize); // Recargar datos para reflejar la eliminación
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
    let valid = true;
    inputs.forEach(input => {
        if (input.required && !input.value) {
            valid = false;
        }
    });
    return valid;
}

function showMessage(message, type) {
    let messageContainer = document.getElementById('message');
    messageContainer.textContent = message;
    messageContainer.className = type; // 'success' o 'error'
    messageContainer.style.display = 'block';
    setTimeout(() => messageContainer.style.display = 'none', 3000);
}

function cancelEdit() {
    fetchSubcategorias(currentPage, pageSize);
}

function cancelCreate() {
    document.getElementById('createRow').remove();
    document.getElementById('createButton').style.display = 'inline-block';
}

function getCurrentDate() {
    let today = new Date();
    let year = today.getFullYear();
    let month = ('0' + (today.getMonth() + 1)).slice(-2);
    let day = ('0' + today.getDate()).slice(-2);
    return `${year}-${month}-${day}`;
}

// Eventos de paginación
document.getElementById('prevPage').addEventListener('click', () => changePage(currentPage - 1));
document.getElementById('nextPage').addEventListener('click', () => changePage(currentPage + 1));
