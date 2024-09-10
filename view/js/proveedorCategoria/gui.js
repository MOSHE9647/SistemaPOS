

function renderTable(categorias) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';
    console.log(categorias);
    categorias.forEach(categoria => {
        let row = `
            <tr data-id="${categoria.ID}">
                <td data-field="nombre">${categoria.Nombre}</td>
                <td data-field="descripcion">${categoria.Descripcion}</td>
                <td>
                    <button onclick=" makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="removeCategoriaFromProveedor(${categoria.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });

    checkEmptyTable();
}

function makeRowEditable(row) {
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        const field = cell.dataset.field;

        if (index < lastCellIndex) {
            let inputHTML;
            switch (field) {
                case 'nombre':
                    inputHTML = `<input type="text" value="${value}" required>`;
                    break;
                default:
                    inputHTML = `<input type="text" value="${value}" required>`;
                    break;
            }

            cell.innerHTML = inputHTML;
        } else {
            cell.innerHTML = `
                <button onclick="updateCategoria(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit()">Cancelar</button>
            `;
        }
    });

    // const fieldHandlers = {
    //     'nombre': (value) => `<input type="text" value="${value}">`,
    //     'direccion': (value) => `<input type="text" value="${value}">`
    // };

    // cells.forEach((cell, index) => {
    //     const value = cell.innerText.trim();
    //     const field = cell.dataset.field;

    //     if (index < lastCellIndex) {
    //         const handler = fieldHandlers[field] || ((v) => `
    //             <select data-field="${field}" id="${field}-select" required>
    //                 <option value="${v}">${v}</option>
    //             </select>
    //         `);
    //         cell.innerHTML = handler(value);
    //     } else {
    //         cell.innerHTML = `
    //             <button onclick="updateDireccion(${row.dataset.id})">Guardar</button>
    //             <button onclick="cancelEdit()">Cancelar</button>
    //         `;
    //     }
    // });

        initializeSelects();
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
            <button onclick="addCategoriaToProveedor()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    
    // Inserta la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
    initializeSelects();
}

function cancelEdit() {
    // Recargar datos de direcciones para cancelar la edición
    const cancelBtn = document.getElementById('cancelEdit');
    if (cancelBtn) { fetchCategoria(proveedor, currentPage, pageSize, sort); }
}

function cancelCreate() {
    // Eliminar la fila de creación
    const createRow = document.getElementById('createRow');
    if (createRow) { createRow.remove(); }

    // Mostrar el botón de crear
    const createBtn = document.getElementById('createButton');
    if (createBtn) { createBtn.style.display = 'inline-block'; }
}