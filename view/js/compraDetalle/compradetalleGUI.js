// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
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


function renderTable(compraDetalles) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    // Renderizar la fila de creación siempre al inicio
    renderCreateRow();

    compraDetalles.forEach(compradetalle => {
        console.log("Detalle de compra:", compradetalle); // Verifica qué detalles llegan

        const precioProducto = isNaN(parseFloat(compradetalle.PrecioProducto)) ? 0 : parseFloat(compradetalle.PrecioProducto);
        console.log("Precio Producto procesado:", precioProducto); // Depura si el precio se procesa correctamente

        const lote = compradetalle.LoteCodigo ? compradetalle.LoteCodigo : 'No disponible';
        const compraa = compradetalle.CompraNumeroFactura ? compradetalle.CompraNumeroFactura : 'No disponible';
        const productoNombre = compradetalle.ProductoNombre ? compradetalle.ProductoNombre : 'Sin Nombre'; // Muestra el nombre del producto

        let row = `
<tr data-id="${compradetalle.ID}" data-loti-id="${compradetalle.Lote}" data-compri-id="${compradetalle.Compra}" data-producti-id="${compradetalle.Producto}">

            <td data-field="compranumerofactura">${compraa}</td> 
            <td data-field="lotecodigo">${lote}</td>
            <td data-field="productonombre">${productoNombre || 'Sin ID'}</td>
            <td data-field="precioproducto">${precioProducto.toFixed(2)}</td>
            <td data-field="cantidad">${compradetalle.Cantidad || '0'}</td>
            <td data-field="fechacreacion">${formatDateToLong(compradetalle.FechaCreacion)}</td>
            <td data-field="fechamodificacion">${formatDateToLong(compradetalle.FechaModificacion)}</td>
            <td>
                <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                <button onclick="deleteCompraDetalle(${compradetalle.ID})">Eliminar</button>
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
        <td data-field="compranumerofactura">
            <select id="compraid-select" required></select>
        </td>
        <td data-field="lotecodigo">
            <select id="loteid-select" required></select>
        </td>
        <td data-field="productonombre">
            <select id="productoid-select" required></select>
        </td>
        <td data-field="precioproducto"><input type="number" step="0.01" required></td>
        <td data-field="cantidad"><input type="number" required></td>
        <td data-field="fechacreacion"><input type="date" value="${today}" min="${today}" required></td>
        <td data-field="fechamodificacion"><input type="date" value="${today}" min="${today}" required></td>
        <td>
            <button onclick="createCompraDetalle()">Crear</button>
        </td>
    `;

    tableBody.appendChild(newRow);

    // Cargar las opciones para los comboboxes
    loadOptions();          // Para lotes
    loadComprasOptions();   // Para compras
    loadProductosOptions(); // Para productos
}


/**
 * Carga las opciones de proveedores en el combobox.
 */
function loadOptions() {
    fetch('../controller/loteAction.php?accion=listarCompraDetalleLotes')
        .then(response => response.json())
        .then(data => {

            console.log("Datos recibidos desde el servidor:", data); // Verifica qué está llegando desde el servidor
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar lotes');
            }

            const selectElement = document.getElementById('loteid-select');
            selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';  // Limpiar opciones existentes

            data.listaCompraDetalleLotes.forEach(lote => {
                const option = new Option(lote.Codigo, lote.ID);
                selectElement.appendChild(option);
            });

            
        })
        .catch(error => {
            console.error('Error al cargar lotes:', error);
        });
}

function loadComprasOptions() {
    fetch('../controller/compraAction.php?accion=listarCompraDetalleCompra')
        .then(response => response.json())
        .then(data => {

            console.log("Datos de compras recibidos desde el servidor:", data); // Verifica qué está llegando desde el servidor
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar compras');
            }

            const selectElement = document.getElementById('compraid-select');
            selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';  // Limpiar opciones existentes

            data.listaCompraDetalleCompra.forEach(compra => {
                const option = new Option(compra.NumeroFactura, compra.ID);
                selectElement.appendChild(option);
            });

        })
        .catch(error => {
            console.error('Error al cargar compras:', error);
        });
}

function loadProductosOptions() {
    fetch('../controller/productoAction.php?accion=listarCompraDetalleProductos')
        .then(response => response.json())
        .then(data => {
            console.log("Datos de productos recibidos desde el servidor:", data); // Depuración

            if (!data.success) {
                throw new Error(data.message || 'Error al cargar productos');
            }

            const selectElement = document.getElementById('productoid-select'); // Selecciona el combobox de productos
            if (!selectElement) {
                console.error('No se encontró el combobox de productos');
                return;
            }

            selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';  // Limpiar las opciones existentes

            data.listaCompraDetalleProducto.forEach(producto => {
                const option = new Option(producto.ProductoNombre, producto.ID); // Agregar las opciones de producto
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
        });
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
    const today = new Date().toISOString().split('T')[0];

    // Obtener el ID del lote de los atributos de la fila
    const lotiId = row.getAttribute('data-loti-id');
    const compriId = row.getAttribute('data-compri-id');
    const productiId = row.getAttribute('data-producti-id');
    
    // Mensaje de depuración para verificar que se está obteniendo el ID correcto
    console.log("Lote ID en makeRowEditable:", lotiId);
    console.log("Compra ID en makeRowEditable:", compriId);
    console.log("Producto ID en makeRowEditable:", productiId);


    cells.forEach((cell, index) => {
        const value = cell.innerText.trim();
        const field = cell.dataset.field;
    
        if (index < lastCellIndex) {
            if (field === 'lotecodigo') {
                cell.innerHTML = `<select id='loteid-select-${row.dataset.id}' required></select>`;
                loadOptionsForEdit(lotiId, row.dataset.id);
            } else if (field === 'compranumerofactura') {
                cell.innerHTML = `<select id='compraid-select-${row.dataset.id}' required></select>`;
                loadComprasOptionsForEdit(compriId, row.dataset.id);
            } else if (field === 'productonombre') {
                cell.innerHTML = `<select id='productoid-select-${row.dataset.id}' required></select>`;
                loadProductosOptionsForEdit(productiId, row.dataset.id); // Cargar productos para editar
            }else if (field === 'fechacreacion') {
                const isoValue = convertLongDateToISO(value);
                cell.innerHTML = `<input type="date" value="${isoValue}"  " required>`;
            } else if (field ==='fechamodificacion') {
                const isoValue = convertLongDateToISO(value);
                cell.innerHTML = `<input type="date" value="${isoValue}"  min="${today}" " required>`;
            } else if (field === 'precioproducto') {
                // Convertir montos a inputs numéricos
                const numberValue = parseFloat(value) || 0; // Asegurarse de manejar correctamente valores numéricos
                cell.innerHTML = `<input type="number" step="0.01" value="${numberValue.toFixed(2)}" required>`;
            } else if (field === 'cantidad') {
                const cantidadValue = parseFloat(value) || 0; // Asegurarse de manejar correctamente valores numéricos
                cell.innerHTML = `<input type="number" step="1" value="${cantidadValue}" required>`;
            } else {
                // Si es un campo de texto o número, mostrar un input editable
                cell.innerHTML = `<input type="text" value="${value}" required>`;
            }
        } else {
            cell.innerHTML = `
                <button onclick="updateCompraDetalle(${row.dataset.id})">Guardar</button>
                <button onclick="cancelEditCompraDetalle()">Cancelar</button>
            `;
        }
    });
}

function loadOptionsForEdit(selectedLotiId, rowId) {
    fetch('../controller/loteAction.php?accion=listarCompraDetalleLotes')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar lotes');
            }

            const selectElement = document.getElementById(`loteid-select-${rowId}`);
            selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';  // Limpiar opciones existentes

            data.listaCompraDetalleLotes.forEach(lote => {
                const isSelected = lote.ID == selectedLotiId; // Compara el ID del proveedor
                const option = new Option(lote.Codigo, lote.ID, isSelected, isSelected);
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar lotes:', error);
        });
        
}


function loadComprasOptionsForEdit(selectedCompriId, rowId) {
    fetch('../controller/compraAction.php?accion=listarCompraDetalleCompra')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar compras');
            }
            const selectElement = document.getElementById(`compraid-select-${rowId}`);
            selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';  // Limpiar opciones existentes
            data.listaCompraDetalleCompra.forEach(compra => {
                const isSelected = compra.ID == selectedCompriId; // Compara el ID de la compra
                const option = new Option(compra.NumeroFactura, compra.ID, isSelected, isSelected);
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar compras:', error);
        });
}

function loadProductosOptionsForEdit(selectedProductiId, rowId) {
    fetch('../controller/productoAction.php?accion=listarCompraDetalleProductos')
        .then(response => response.json())
        .then(data => {
            console.log('Datos de productos recibidos del servidor:', data); // Depuración

            if (!data.success) {
                throw new Error(data.message || 'Error al cargar productos');
            }

            // Selecciona el combobox correspondiente a la fila
            const selectElement = document.getElementById(`productoid-select-${rowId}`);
            if (!selectElement) {
                console.error(`No se encontró el combobox de productos con ID: productoid-select-${rowId}`);
                return;
            }

            // Limpia las opciones actuales
            selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';

            // Agrega los productos al combobox
            data.listaCompraDetalleProducto.forEach(producto => {
                const isSelected = producto.ID == selectedProductiId; // Verifica si el producto es el seleccionado
                const option = new Option(producto.ProductoNombre, producto.ID, isSelected, isSelected);
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar productos:', error); // Error en consola
        });
}

