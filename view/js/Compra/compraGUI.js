// ************************************************************ //
// ****************** Funciones de Interfaz de Usuario ******** //
// ************************************************************ //

/**
 * Formatea una fecha en formato ISO a una fecha en formato largo.
 * 
 * @param {string} date - Fecha en formato ISO (e.g., "2024-09-09").
 * @returns {string} - Fecha en formato largo (e.g., "09 de septiembre de 2024").
 */
function formatDateToLong(date) {
    const months = [
        "enero", "febrero", "marzo", "abril", "mayo", "junio",
        "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
    ];
    const [year, month, day] = date.split('-');
    const monthName = months[parseInt(month, 10) - 1];

    return `${parseInt(day, 10)} de ${monthName} de ${year}`;
}

/**
 * Convierte una fecha en formato largo a formato ISO.
 * 
 * @param {string} date - Fecha en formato largo (e.g., "09 de septiembre de 2024").
 * @returns {string} - Fecha en formato ISO (e.g., "2024-09-09").
 */
function convertLongDateToISO(date) {
    const months = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];

    const [day, monthName, year] = date.split(' de ');
    const monthIndex = months.indexOf(monthName) + 1;
    const formattedMonth = String(monthIndex).padStart(2, '0');
    const formattedDay = String(parseInt(day, 10)).padStart(2, '0');

    return `${year}-${formattedMonth}-${formattedDay}`;
}

/**
 * Renderiza la tabla con las compras proporcionadas y muestra la fila para crear una nueva compra.
 * 
 * @param {Array} compras - Un arreglo de objetos de compra.
 */
function renderTable(compras) {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    // Renderizar la fila de creación siempre al inicio
    renderCreateRow();

    // Renderizar las filas de las compras existentes
    compras.forEach(compra => {
        const montoBruto = isNaN(parseFloat(compra.MontoBruto)) ? 0 : parseFloat(compra.MontoBruto);
        const montoNeto = isNaN(parseFloat(compra.MontoNeto)) ? 0 : parseFloat(compra.MontoNeto);
        const proveedor = compra.Proveedor ? compra.Proveedor : 'No disponible';

        const row = `
            <tr data-id="${compra.ID}" data-provider-id="${compra.Proveedor}">
                <td data-field="numerofactura">${compra.NumeroFactura}</td>
                <td data-field="proveedornombre">${proveedor}</td>
                <td data-field="montobruto">${montoBruto.toFixed(2)}</td>
                <td data-field="montoneto">${montoNeto.toFixed(2)}</td>
                <td data-field="tipopago">${compra.TipoPago}</td>
                <td data-field="fechacreacion">${formatDateToLong(compra.FechaCreacion)}</td>
                <td data-field="fechamodificacion">${formatDateToLong(compra.FechaModificacion)}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteCompra(${compra.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}

/**
 * Muestra la fila para crear una nueva compra siempre visible al inicio de la tabla.
 */
function renderCreateRow() {
    const tableBody = document.getElementById('tableBody');
    const today = new Date().toISOString().split('T')[0]; // Fecha de hoy en formato 'YYYY-MM-DD'

    const newRow = document.createElement('tr');
    newRow.id = 'createRow';
    newRow.innerHTML = `
        <td data-field="numerofactura"><input type="text" required></td>
        <td data-field="proveedornombre">
            <select id="proveedorid-select" required></select>
        </td>
        <td data-field="montobruto"><input type="number" step="0.01" required></td>
        <td data-field="montoneto"><input type="number" step="0.01" required></td>
        <td data-field="tipopago">
            <select required>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
            </select>
        </td>
        <td data-field="fechacreacion"><input type="date" value="${today}" required></td>
        <td data-field="fechamodificacion"><input type="date" value="${today}" required></td>
        <td>
            <button onclick="createCompra()">Crear</button>
        </td>
    `;

    tableBody.appendChild(newRow);
    // Cargar opciones para el combobox de proveedores
    loadOptions();
}

/**
 * Carga las opciones de proveedores en el combobox.
 */
function loadOptions() {
    fetch('../controller/proveedorAction.php?accion=listarCompraProveedores')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar proveedores');
            }

            const selectElement = document.getElementById('proveedorid-select');
            selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';  // Limpiar opciones existentes

            data.listaCompraProveedores.forEach(proveedor => {
                const option = new Option(proveedor.Nombre, proveedor.ID);
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar proveedores:', error);
        });
}

/**
 * Cancela la edición de una compra.
 */
function cancelEdit() {
    fetchCompras(currentPage, pageSize); // Recargar datos para cancelar la edición
}

function makeRowEditable(row) {
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    // Obtener el ID del proveedor de los atributos de la fila
    const providerId = row.getAttribute('data-provider-id');
    
    // Mensaje de depuración para verificar que se está obteniendo el ID correcto
    console.log("Proveedor ID en makeRowEditable:", providerId);

    // Recorre cada celda para convertirla en editable
    cells.forEach((cell, index) => {
        const value = cell.innerText.trim(); // Valor actual de la celda
        const field = cell.dataset.field; // Campo de la tabla que se está editando

        // Si la celda no es la última (que contiene los botones de acción)
        if (index < lastCellIndex) {
            if (field === 'proveedornombre') {
                // Cambiar la celda a un select para editar el proveedor
                cell.innerHTML = `<select id='proveedorid-select-${row.dataset.id}' required></select>`;
                // Cargar las opciones de proveedores y seleccionar el actual
                loadOptionsForEdit(providerId, row.dataset.id);
            } else if (field === 'fechacreacion' || field === 'fechamodificacion') {
                const isoValue = convertLongDateToISO(value);
                cell.innerHTML = `<input type="date" value="${isoValue}" required>`;
            } else if (field === 'tipopago') {
                // Crear un select para los métodos de pago
                cell.innerHTML = ` 
                    <select required>
                        <option value="efectivo" ${value === 'efectivo' ? 'selected' : ''}>Efectivo</option>
                        <option value="tarjeta" ${value === 'tarjeta' ? 'selected' : ''}>Tarjeta</option>
                        <option value="transferencia" ${value === 'transferencia' ? 'selected' : ''}>Transferencia</option>
                    </select>`;
                } else if (field === 'montobruto' || field === 'montoneto') {
                    // Convertir montos a inputs numéricos
                    const numberValue = parseFloat(value) || 0; // Asegurarse de manejar correctamente valores numéricos
                    cell.innerHTML = `<input type="number" step="0.01" value="${numberValue.toFixed(2)}" required>`;
                } else {
                    // Si es un campo de texto o número, mostrar un input editable
                    cell.innerHTML = `<input type="text" value="${value}" required>`;
                }
        } else {
            // Última celda: botones para guardar o cancelar
            cell.innerHTML = `
                <button onclick="updateCompra(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit()">Cancelar</button>
            `;
        }
    });
}
function loadOptionsForEdit(selectedProviderId, rowId) {
    fetch('../controller/proveedorAction.php?accion=listarCompraProveedores')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar proveedores');
            }

            const selectElement = document.getElementById(`proveedorid-select-${rowId}`);
            selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';  // Limpiar opciones existentes

            data.listaCompraProveedores.forEach(proveedor => {
                const isSelected = proveedor.ID == selectedProviderId; // Compara el ID del proveedor
                const option = new Option(proveedor.Nombre, proveedor.ID, isSelected, isSelected);
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar proveedores:', error);
        });
}
