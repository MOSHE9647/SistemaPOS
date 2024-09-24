// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza una tabla con los productos proporcionados.
 * 
 * @param {Array} productos - Un arreglo de objetos de producto.
 * @example
 * const productos = [
 *   { productoid: 1, productonombre: 'Producto 1', productoprecio: 100.00, productostock: 50, productocategoria: 'Categoría 1', productofechaingreso: '2024-01-01' },
 *   { productoid: 2, productonombre: 'Producto 2', productoprecio: 200.00, productostock: 30, productocategoria: 'Categoría 2', productofechaingreso: '2024-02-01' },
 * ];
 * renderProductosTable(productos);
 */
function formatPrecio(precio) {
    let parsedPrecio = parseFloat(precio);
    return !isNaN(parsedPrecio) ? parsedPrecio.toFixed(2) : '0.00';
}

function renderTable(productos) {
    let tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Limpia la tabla

    productos.forEach(producto => {
        const {
            ID = producto,
            CodigoBarrasNumero = 'No disponible',
            ProductoNombre = 'Desconocido',
            PrecioCompra = '0.00',
            ProductoPorcentajeGanancia = 'No disponible',
            ProductoDescripcion = 'Sin descripción',
            CategoriaNombre = 'No disponible',
            SubCategoriaNombre = 'No disponible',
            MarcaNombre = 'No disponible',
            PresentacionNombre = 'No disponible',
            ProductoImagen = 'placeholder.jpg',
            ProductoEstado
        } = producto;

        let precioFormatted = formatPrecio(PrecioCompra);

        let row = `
            <tr data-id="${ID}">
                <td data-field="codigobarrasnombre">${CodigoBarrasNumero}</td>
                <td data-field="productonombre">${ProductoNombre}</td>
                <td data-field="productopreciocompra">${precioFormatted}</td>
                <td data-field="productoporcentajeganancia">${ProductoPorcentajeGanancia}</td>
                <td data-field="productodescripcion">${ProductoDescripcion}</td> 
                <td data-field="categorianombre">${CategoriaNombre}</td> 
                <td data-field="subcategorianombre">${SubCategoriaNombre}</td> 
                <td data-field="marcanombre">${MarcaNombre}</td> 
                <td data-field="presentacionnombre">${PresentacionNombre}</td> 
                <td data-field="productoimagen"><img src="${ProductoImagen}" alt="Producto Imagen" width="50" height="50"></td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteProducto(${ID})">Eliminar</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}



/**
 * Convierte una fila de la tabla de productos en editable.
 * 
 * @param {HTMLElement} row - La fila que se desea convertir en editable.
 * @description Convierte los elementos de la fila en inputs para editar los valores, y agrega botones para guardar o cancelar los cambios.
 * @example
 * makeProductRowEditable(document.getElementById('producto1'));
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

    initializeSelects();
}

/**
 * Muestra la fila para crear un nuevo producto.
 * 
 * @description Oculta el botón de crear y agrega una nueva fila a la tabla para ingresar los datos del producto.
 * @example
 * showCreateProductoRow();
 */
function showCreateRow() {
    document.getElementById('createButton').style.display = 'none';

    let tableBody = document.getElementById('tableBody');
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    newRow.innerHTML = `
        <td data-field="nombre"><input type="text" required></td>
        <td data-field="precioCompra"><input type="number" step="0.01" required></td>
        <td data-field="ganancia"><input type="number" step="0.01" required></td>
        <td data-field="categoria">
            <select id="categoria-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="subcategoria">
            <select id="subcategoria-select" required>
                <option value="">-- Seleccionar --</option>
            </select>
        </td>
        <td data-field="descripcion"><input type="text" required></td>
        <td data-field="codigoBarrasID"><input type="text" required></td>
        <td data-field="foto"><input type="text" required></td>
        <td>
            <button onclick="createProducto()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    tableBody.insertBefore(newRow, tableBody.firstChild);

    initializeSelects();
}

/**
 * Cancela la edición de un producto.
 * 
 * @description Recarga los datos de productos para cancelar la edición en curso.
 * @example
 * cancelEditProducto();
 */
function cancelEditProducto() {
    fetchProductos(currentPage, pageSize); // Recargar datos para cancelar la edición
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