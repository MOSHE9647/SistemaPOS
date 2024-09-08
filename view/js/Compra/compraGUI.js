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
 * Renderiza la tabla con las compras proporcionadas.
 * 
 * @param {Array} compras - Un arreglo de objetos de compra.
 */
function renderTable(compras) {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    compras.forEach(compra => {

         // Verifica si MontoBruto y MontoNeto son números
         const montoBruto = isNaN(parseFloat(compra.MontoBruto)) ? 0 : parseFloat(compra.MontoBruto);
         const montoNeto = isNaN(parseFloat(compra.MontoNeto)) ? 0 : parseFloat(compra.MontoNeto);
         //<td data-field="proveedorid">${compra.ProveedorID}</td>
        const row = `
            <tr data-id="${compra.ID}">
                <td data-field="numerofactura">${compra.NumeroFactura}</td>
              
                <td data-field="proveedornombre">${compra.ProveedorNombre}</td> <!-- Muestra el nombre del proveedor -->
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
 * Formatea una fecha para que solo incluya el componente de la fecha (sin horas).
 * 
 * @param {string} date - La fecha en formato de cadena.
 * @returns {string} La fecha formateada en formato 'YYYY-MM-DD'.
 */
function formatDate(date) {
    // Asumimos que la fecha viene en formato 'YYYY-MM-DDTHH:MM:SS' o similar y queremos solo 'YYYY-MM-DD'
    return date.split('T')[0];
}

/**
 * Convierte una fila en editable.
 * 
 * @param {HTMLElement} row - La fila que se desea convertir en editable.
 * @description Convierte los elementos de la fila en inputs para editar los valores, y agrega botones para guardar o cancelar los cambios.
 * @example
 * makeRowEditable(document.getElementById('fila1'));
 */
function makeRowEditable(row) {
    const cells = row.querySelectorAll('td');
    const lastCellIndex = cells.length - 1;

    // Obtener la fecha actual en formato 'YYYY-MM-DD'
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const formattedToday = `${yyyy}-${mm}-${dd}`;

    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        const field = cell.dataset.field;

        /*if (index < lastCellIndex) {
            if (field === 'fechacreacion' || field === 'fechamodificacion') {
                const isoValue = convertLongDateToISO(value);
                cell.innerHTML = `<input type="date" value="${isoValue}" min="${formattedToday}" required>`;
            } else {
                cell.innerHTML = `<input type="text" value="${value}" required>`;
            }
        } else {
         */  if (index < lastCellIndex) {
            if (field === 'fechacreacion' || field === 'fechamodificacion') {
                const isoValue = convertLongDateToISO(value);
                cell.innerHTML = `<input type="date" value="${isoValue}" min="${formattedToday}" required>`;
            } else if (field === 'tipopago') {
                // Crear un combobox para los métodos de pago
                cell.innerHTML = `
                    <select required>
                        <option value="efectivo" ${value === 'efectivo' ? 'selected' : ''}>Efectivo</option>
                        <option value="tarjeta" ${value === 'tarjeta' ? 'selected' : ''}>Tarjeta</option>
                        <option value="transferencia" ${value === 'transferencia' ? 'selected' : ''}>Transferencia</option>
                    </select>
                `;
            } else if (field === 'proveedornombre') {
                // Crear un combobox para los nombres de proveedores
                cell.innerHTML = `<select id="proveedorid-select" required></select>`;
                loadOptions('proveedor', row.dataset.proveedorid); // Carga las opciones del combobox de proveedores
            } else {
                cell.innerHTML = `<input type="text" value="${value}" required>`;
            }
        } else {
            cell.innerHTML = `
                <button onclick="updateCompra(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEdit()">Cancelar</button>
            `;
        }
    });
}

/**
 * Carga las opciones para los comboboxes de producto y proveedor.
 * 
 * @param {string} field - El tipo de campo ('producto' o 'proveedor').
 * @param {string} selectedValue - El valor seleccionado actualmente.
 */

function loadOptions(field, selectedValue) {
    const url = field === 'proveedor'
    ? '../controller/proveedorAction.php'
    : '../controller/compraAction.php'; // Ajusta esto según tus necesidades

    //const selectElement = document.getElementById(`${field}-select`);
    const selectElement = document.getElementById(`${field}id-select`);
    if (!selectElement) {
        console.error('Elemento select no encontrado:', `${field}id-select`);
        return;
    }
    
    fetch(url)
        .then(response => response.json()) // Lee la respuesta como JSON
        .then(data => {
            console.log('Respuesta cruda:', data); // Muestra la respuesta cruda

            // Extrae el array correcto basado en el campo
            //const items = field === 'producto' ? data.listaProductos : data.listaProveedores;
            let items;
            if (field === 'proveedor') {
                items = data.listaProveedores; // Asegúrate de que esta propiedad existe en la respuesta
            } 
            
            if (!Array.isArray(items)) {
                throw new Error('Datos recibidos no son un array');
            }

             // Llena el select con las opciones
             selectElement.innerHTML = items.map(item => `
                <option value="${item.proveedorid}" ${item.proveedorid == selectedValue ? 'selected' : ''}>
                    ${item.proveedornombre}
                </option>
            `).join('');
        })
        .catch(error => {
            console.error('Error al cargar opciones:', error);
        });
}

/**
 * Muestra la fila para crear un nuevo lote.
 * 
 * @description Oculta el botón de crear y agrega una nueva fila a la tabla para ingresar los datos del lote.
 * @example
 * showCreateRow();
 */
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    // Obtener la fecha actual en formato 'YYYY-MM-DD'
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const formattedToday = `${yyyy}-${mm}-${dd}`;

    newRow.innerHTML = `
        <td data-field="numerofactura"><input type="text" required></td>
       <td data-field="proveedorid">
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
        <td data-field="fechacreacion">
            <input type="date" value="${formattedToday}" required>
        </td>
        <td data-field="fechamodificacion">
            <input type="date" value="${formattedToday}" required>
        </td>
        <td>
            <button onclick="createCompra()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    tableBody.insertBefore(newRow, tableBody.firstChild);
    loadOptions('proveedor', null);

}



/**
 * Cancela la edición de un lote.
 * 
 * @description Recarga los datos de lotes para cancelar la edición en curso.
 * @example
 * cancelEdit();
 */
function cancelEdit() {
    fetchCompras(currentPage, pageSize); // Recargar datos para cancelar la edición
}

/**
 * Cancela la creación de un nuevo lote.
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