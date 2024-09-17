// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza una tabla con las cuentas por pagar proporcionadas.
 * 
 * @param {Array} cuentasPorPagar - Un arreglo de objetos de cuenta por pagar.
 * @example
 * const cuentasPorPagar = [
 *   { id: 1, compraDetalleID: 101, fechaVencimiento: '2024-10-01', montoTotal: 500.00, montoPagado: 250.00, fechaPago: '2024-09-15', notas: 'Nota 1', estadoCuenta: 'Pendiente', estado: 'Activo' },
 *   { id: 2, compraDetalleID: 102, fechaVencimiento: '2024-11-01', montoTotal: 700.00, montoPagado: 700.00, fechaPago: '2024-09-20', notas: 'Nota 2', estadoCuenta: 'Pagado', estado: 'Activo' },
 * ];
 * renderCuentasPorPagarTable(cuentasPorPagar);
 */
function formatMonto(monto) {
    let parsedMonto = parseFloat(monto);
    return !isNaN(parsedMonto) ? parsedMonto.toFixed(2) : '0.00';
}

/*function renderTable(cuentasPorPagar) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    cuentasPorPagar.forEach(cuenta => {
        let montoTotalFormatted = formatMonto(cuenta.MontoTotal);
        let montoPagadoFormatted = formatMonto(cuenta.MontoPagado);

        let row = `
        <tr data-id="${cuenta.ID}">
            <td data-field="compradetalleid">${cuenta.CompraDetalleID || 'Sin ID'}</td>
            <td data-field="fechavencimiento">${cuenta.FechaVencimiento || 'Sin fecha'}</td>
            <td data-field="montototal">${montoTotalFormatted}</td>
            <td data-field="montopagado">${montoPagadoFormatted}</td>
            <td data-field="fechapago">${cuenta.FechaPago || 'Sin fecha'}</td>
            <td data-field="notas">${cuenta.Notas || 'Sin notas'}</td>
            <td data-field="estadocuenta">${cuenta.EstadoCuenta || 'Desconocido'}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteCuentaPorPagar(${cuenta.ID})">Eliminar</button>
            </td>
        </tr>
        `;
        tableBody.innerHTML += row;
    });

    checkEmptyTable();
}
*/
function renderTable(cuentasPorPagar) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    // Renderizar la fila de creación siempre visible al inicio de la tabla
    renderCreateRow();

    // Renderizar las demás filas
    cuentasPorPagar.forEach(cuenta => {
        let montoTotalFormatted = formatMonto(cuenta.MontoTotal);
        let montoPagadoFormatted = formatMonto(cuenta.MontoPagado);

        let row = `
        <tr data-id="${cuenta.ID}">
            <td data-field="compradetalleid">${cuenta.CompraDetalleID || 'Sin ID'}</td>
            <td data-field="fechavencimiento">${cuenta.FechaVencimiento || 'Sin fecha'}</td>
            <td data-field="montototal">${montoTotalFormatted}</td>
            <td data-field="montopagado">${montoPagadoFormatted}</td>
            <td data-field="fechapago">${cuenta.FechaPago || 'Sin fecha'}</td>
            <td data-field="notas">${cuenta.Notas || 'Sin notas'}</td>
            <td data-field="estadocuenta">${cuenta.EstadoCuenta || 'Desconocido'}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteCuentaPorPagar(${cuenta.ID})">Eliminar</button>
            </td>
        </tr>
        `;
        tableBody.innerHTML += row;
    });

    checkEmptyTable();
}
function renderCreateRow() {
    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    newRow.innerHTML = `
        <td data-field="compradetalleid"><input type="text" required></td>
        <td data-field="fechavencimiento"><input type="date" required></td>
        <td data-field="montototal"><input type="number" step="0.01" required></td>
        <td data-field="montopagado"><input type="number" step="0.01" required></td>
        <td data-field="fechapago"><input type="date" required></td>
        <td data-field="notas"><input type="text" required></td>
        <td data-field="estadocuenta"><input type="text" required></td>
        <td>
            <button onclick="createCuentaPorPagar()">Guardar</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Inserta la fila de creación al inicio de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
}

/**
 * Convierte una fila de la tabla de cuentas por pagar en editable.
 * 
 * @param {HTMLElement} row - La fila que se desea convertir en editable.
 * @description Convierte los elementos de la fila en inputs para editar los valores, y agrega botones para guardar o cancelar los cambios.
 * @example
 * makeRowEditable(document.getElementById('cuenta1'));
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
                case 'id':
                case 'compradetalleid':
                    inputHTML = `<input type="text" value="${value}" disabled>`;
                    break;
                case 'fechavencimiento':
                case 'fechapago':
                    inputHTML = `<input type="date" value="${value}" required>`;
                    break;
                case 'montototal':
                case 'montopagado':
                    inputHTML = `<input type="number" step="0.01" value="${value}" required>`;
                    break;
                case 'notas':
                case 'estadocuenta':
                case 'estado':
                    inputHTML = `<input type="text" value="${value}" required>`;
                    break;
                default:
                    inputHTML = `<input type="text" value="${value}" required>`;
                    break;
            }

            cell.innerHTML = inputHTML;
        } else {
            cell.innerHTML = `
                <button onclick="updateCuentaPorPagar(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEditCuentaPorPagar()">Cancelar</button>
            `;
        }
    });
}

/**
 * Muestra la fila para crear una nueva cuenta por pagar.
 * 
 * @description Oculta el botón de crear y agrega una nueva fila a la tabla para ingresar los datos de la cuenta por pagar.
 * @example
 * showCreateRow();
 */
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    newRow.innerHTML = `
        <td data-field="compradetalleid"><input type="text" required></td>
        <td data-field="fechavencimiento"><input type="date" required></td>
        <td data-field="montototal"><input type="number" step="0.01" required></td>
        <td data-field="montopagado"><input type="number" step="0.01" required></td>
        <td data-field="fechapago"><input type="date" required></td>
        <td data-field="notas"><input type="text" required></td>
        <td data-field="estadocuenta"><input type="text" required></td>
        <td>
            <button onclick="createCuentaPorPagar()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    tableBody.insertBefore(newRow, tableBody.firstChild);
}

/**
 * Cancela la edición de una cuenta por pagar.
 * 
 * @description Recarga los datos de cuentas por pagar para cancelar la edición en curso.
 * @example
 * cancelEditCuentaPorPagar();
 */
function cancelEditCuentaPorPagar() {
    fetchCuentaPorPagar(currentPage, pageSize); // Recargar datos para cancelar la edición
}

/**
 * Cancela la creación de una nueva cuenta por pagar.
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
