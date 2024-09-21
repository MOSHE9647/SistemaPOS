function renderTable(productos){
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML='';
   
    productos.forEach(producto=> {

        let row =`
        <tr data-id="${producto.ID}">
            <td data-field="nombre">${producto.Nombre || 'Desconocido'}</td>
                <td data-field="precioCompra">${producto.PrecioCompra || '0.00'}</td>
                <td data-field="ganancia">${producto.PorcentajeGanancia || 'No disponible'}</td>
                <td data-field="descripcion">${producto.Descripcion || 'Sin descripción'}</td>
                <td data-field="codigoBarrasID">${producto.CodigoBarras || 'Sin código de barras'}</td>
                <td data-field="foto">${producto.Imagen || 'Sin foto'}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteProducto(${producto.ID})">Eliminar</button>
                </td>
        <tr>
        `;
        tableBody.innerHTML += row;
    });

    checkEmptyTable();
}

function makeRowEditable(row){
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
                case 'precioCompra':
                    inputHTML = `<input type="number" step="0.01" value="${value}" required>`;
                    break;
                case 'ganancia':
                    inputHTML = `<input type="number" step="0.01" value="${value}" required>`;
                    break;
                case 'descripcion':
                    inputHTML = `<input type="text" value="${value}" required>`;
                    break;
                case 'codigoBarrasID':
                case 'foto':
                         // Usa un input solo para los campos editables si es necesario
                    inputHTML = `<input type="text" value="${value}" disabled>`;
                    break;
                default:
                    inputHTML = `<input type="text" value="${value}" required>`;
                    break;
            }

            cell.innerHTML = inputHTML;
        } else {
            cell.innerHTML = `
                <button onclick="updateProducto(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEditProducto()">Cancelar</button>
            `;
        }
    });

}
function showCreateRow(){
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    newRow.innerHTML =`
        <td data-field="nombre"><input type="text" required></td>
        <td data-field="precioCompra"><input type="number" step="0.01" required></td>
        <td data-field="ganancia"><input type="number" step="0.01" required></td>
        <td data-field="descripcion"><input type="text" required></td>
        <td data-field="codigoBarrasID"><input type="text" required></td>
        <td data-field="foto"><input type="text" required></td>
        <td>
            <button onclick="createProducto()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;
    tableBody.insertBefore(newRow,tableBody.firstChild);
}
function cancelEditProducto() {
    fetchProductos(proveedor, currentPage, pageSize, sort);
}

function cancelCreate() {
    const createRow = document.getElementById('createRow');
    if (createRow) {
        createRow.remove();
    }
    document.getElementById('createButton').style.display = 'inline-block';
}