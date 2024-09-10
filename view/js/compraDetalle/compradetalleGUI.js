// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza una tabla con los detalles de compra proporcionados.
 * 
 * @param {Array} compraDetalles - Un arreglo de objetos de detalle de compra.
 * @example
 * const compraDetalles = [
 *   { id: 1, productoID: 201, cantidad: 10, precioUnitario: 50.00, subtotal: 500.00, estado: 'Activo' },
 *   { id: 2, productoID: 202, cantidad: 5, precioUnitario: 100.00, subtotal: 500.00, estado: 'Activo' },
 * ];
 * renderCompraDetallesTable(compraDetalles);
 */
function formatMonto(monto) {
    let parsedMonto = parseFloat(monto);
    return !isNaN(parsedMonto) ? parsedMonto.toFixed(2) : '0.00';
}

function renderTable(compraDetalles) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    compraDetalles.forEach(detalle => {
        let precioProductoFormatted = formatMonto(detalle.PrecioProducto);
       

        let row = `
        <tr data-id="${detalle.ID}">
            <td data-field="compraid">${detalle.CompraID || 'Sin ID'}</td> 
            <td data-field="loteid">${detalle.LoteID || 'Sin ID'}</td>
            <td data-field="productoid">${detalle.ProductoID || 'Sin ID'}</td>
            <td data-field="precioproducto">${precioProductoFormatted}</td>
            <td data-field="cantidad">${detalle.Cantidad || '0'}</td>
            <td data-field="fechacreacion">${detalle.FechaCreacion}</td>
            <td data-field="fechamodificacion">${detalle.FechaModificacion || 'Desconocido'}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteCompraDetalle(${detalle.ID})">Eliminar</button>
            </td>
        </tr>
        `;
        tableBody.innerHTML += row;
    });

    checkEmptyTable();
}

/**
 * Convierte una fila de la tabla de detalles de compra en editable.
 * 
 * @param {HTMLElement} row - La fila que se desea convertir en editable.
 * @description Convierte los elementos de la fila en inputs para editar los valores, y agrega botones para guardar o cancelar los cambios.
 * @example
 * makeRowEditable(document.getElementById('detalle1'));
 */
function makeRowEditable(row) {
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        const field = cell.dataset.field;

        if (index < lastCellIndex) {
            let inputHTML;

            switch (field) {
                case 'compraid':
                case 'loteid':
                case 'productoid':
                    inputHTML = `<input type="text" value="${value}" disabled>`;
                    break;
                case 'cantidad':
                    inputHTML = `<input type="number" value="${value}" required>`;
                    break;
                case 'precioproducto':
                    inputHTML = `<input type="number" step="0.01" value="${value}" required>`;
                    break;
                case 'fechacreacion':
                case 'fechamodificacion':
                    inputHTML = `<input type="date" value="${value}" required>`;
                    break;
                default:
                    inputHTML = `<input type="text" value="${value}" required>`;
                    break;
            }

            cell.innerHTML = inputHTML;
        } else {
            cell.innerHTML = `
                <button onclick="updateCompraDetalle(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEditCompraDetalle()">Cancelar</button>
            `;
        }
    });
}

/**
 * Muestra la fila para crear un nuevo detalle de compra.
 * 
 * @description Oculta el botón de crear y agrega una nueva fila a la tabla para ingresar los datos del detalle de compra.
 * @example
 * showCreateRow();
 */
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    newRow.innerHTML = `
         <td data-field="compraid"><input type="text" required></td>
        <td data-field="loteid"><input type="text" required></td>
        <td data-field="productoid"><input type="text" required></td>
        <td data-field="precioproducto"><input type="number" step="0.01" required></td>
        <td data-field="cantidad"><input type="number" required></td>
        <td data-field="fechacreacion"><input type="date" required></td>
        <td data-field="fechamodificacion"><input type="date" required></td>
        <td>
            <button onclick="createCompraDetalle()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    tableBody.insertBefore(newRow, tableBody.firstChild);
}

/**
 * Cancela la edición de un detalle de compra.
 * 
 * @description Recarga los datos de detalles de compra para cancelar la edición en curso.
 * @example
 * cancelEditCompraDetalle();
 */
function cancelEditCompraDetalle() {
    fetchCompraDetalles(currentPage, pageSize); // Recargar datos para cancelar la edición
}

/**
 * Cancela la creación de un nuevo detalle de compra.
 * 
 * @description Elimina la fila de creación y muestra nuevamente el botón de crear.
 * @example
 * cancelCreate();
 */
function cancelCreate() {
    console.log('Cancelando creación, mostrando botón de crear.');
    const createRow = document.getElementById('createRow');
    if (createRow) {
        createRow.remove();
    }

    document.getElementById('createButton').style.display = 'inline-block';
}
