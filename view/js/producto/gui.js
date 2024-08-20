// ************************************************************ //
// ************* Métodos para el manejo de la GUI ************* //
// ************************************************************ //

/**
 * Renderiza la tabla de productos con la lista de productos proporcionada.
 * 
 * @description Esta función renderiza la tabla de productos con la lista de productos proporcionada.
 *              Vacía el cuerpo de la tabla y luego itera sobre cada producto en la lista,
 *              creando una fila para cada producto y agregándola al cuerpo de la tabla.
 * 
 * @param {array} productos - La lista de productos a renderizar
 * 
 * @example
 * let productos = [...]; // Lista de productos
 * renderTable(productos);
 */
function renderTable(productos) {
    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');
    
    // Vaciar el cuerpo de la tabla
    tableBody.innerHTML = '';

    // Recorrer cada producto en el arreglo
    productos.forEach(producto => {
        // Crear una fila para el producto
        let row = `
            <tr data-id="${producto.ID}">
                <td data-field="nombre">${producto.Nombre}</td>
                <td data-field="precio">${producto.Precio}</td>
                <td data-field="cantidad">${producto.Cantidad}</td>
                <td data-field="fecha" data-iso="${producto.FechaISO}">${producto.Fecha}</td>
                <td data-field="descripcion">${producto.Descripcion}</td>
                <td data-field="codigo">${producto.CodigoBarras}</td>
                <td>
                    <button onclick="makeRowEditable(this.parentNode.parentNode)">Editar</button>
                    <button onclick="deleteProducto(${producto.ID})">Eliminar</button>
                </td>
            </tr>
        `;
        
        // Agregar la fila al cuerpo de la tabla
        tableBody.innerHTML += row;
    });
}

/**
 * Obtiene la fecha actual en formato YYYY-MM-DD.
 * 
 * @description Esta función devuelve la fecha actual en formato de cadena, con el año en cuatro dígitos,
 *              el mes en dos dígitos (con cero a la izquierda si es necesario) y el día en dos dígitos
 *              (con cero a la izquierda si es necesario).
 * 
 * @returns {string} La fecha actual en formato YYYY-MM-DD
 * 
 * @example
 * let currentDate = getCurrentDate();
 * console.log(currentDate); // Imprime la fecha actual, por ejemplo: "2023-07-25"
 */
function getCurrentDate() {
    let today = new Date();
    let year = today.getFullYear();
    let month = (today.getMonth() + 1).toString().padStart(2, '0');
    let day = today.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Hace editable una fila de la tabla de productos.
 * 
 * @description Esta función selecciona todas las celdas de la fila y, para cada una,
 *              reemplaza su contenido con un campo de entrada editable correspondiente al tipo de dato.
 *              Los campos de fecha, nombre y precio tienen validaciones y restricciones específicas.
 *              La última celda se reemplaza con botones para guardar o cancelar los cambios.
 * 
 * @param {HTMLElement} row - La fila de la tabla a hacer editable
 * 
 * @example
 * makeRowEditable(document.querySelector('tr[data-id="1"]'));
 * 
 * @returns {void}
 */
function makeRowEditable(row) {
    // Seleccionar todas las celdas de la fila
    const cells = row.querySelectorAll('td');
    
    // Definir funciones para manejar cada tipo de campo
    const fieldHandlers = {
        'fecha': (value) => {
            // Crear un campo de fecha con el valor actual y una fecha máxima igual a la fecha actual
            return `<input type="date" value="${value}" max="${getCurrentDate()}" required>`;
        },
        'nombre': (value) => {
            // Crear un campo de texto con el valor actual
            return `<input type="text" value="${value}" required>`;
        },
        'codigo': (value) => {
            // Crear un campo de texto con el valor actual y deshabilitado
            return `<input type="text" value="${value}" disabled>`;
        },
        'cantidad': (value) => {
            // Crear un campo numérico con el valor actual y restricciones de mínimo
            return `<input type="number" value="${value}" min="0" required>`;
        },
        'precio': (value) => {
            // Convertir el precio a double y limitar a 2 decimales
            const formattedValue = parseFloat(value).toFixed(2);
            // Crear un campo numérico con el precio formateado y restricciones de mínimo y paso
            return `<input type="number" value="${formattedValue}" min="0" step="0.01" required>`;
        }
    };

    // Recorrer cada celda de la fila
    cells.forEach((cell, index) => {
        // Excluir la última columna
        if (index < cells.length - 1) {
            // Obtener el campo y valor de la celda
            const field = cell.dataset.field;
            const value = field === 'fecha' ? cell.dataset.iso : cell.innerText;
            // Obtener la función de manejo para el campo o una función default
            const handler = fieldHandlers[field] || ((v) => `<input type="text" value="${v}">`);
            // Reemplazar el contenido de la celda con el campo editable
            cell.innerHTML = handler(value);
        }
    });

    // Obtener la última celda de la fila
    const actionCell = cells[cells.length - 1];
    // Reemplazar el contenido de la celda con botones para guardar o cancelar
    actionCell.innerHTML = `
        <button onclick="updateProducto(${row.dataset.id})">Guardar</button>
        <button onclick="cancelEdit(${row.dataset.id})">Cancelar</button>
    `;
}

/**
 * Muestra una fila para crear un nuevo producto en la tabla.
 * 
 * @description Esta función oculta el botón de crear y crea una nueva fila en la tabla con campos editables
 *              para ingresar los datos del nuevo producto. La fila incluye botones para crear o cancelar.
 * 
 * @example
 * showCreateRow();
 * 
 * @returns {void}
 */
function showCreateRow() {
    // Ocultar el botón de crear
    document.getElementById('createButton').style.display = 'none';

    // Obtener el cuerpo de la tabla
    let tableBody = document.getElementById('tableBody');

    // Crear una nueva fila para la tabla
    let newRow = document.createElement('tr');
    newRow.id = 'createRow';

    // Definir el contenido de la fila con campos editables
    newRow.innerHTML = `
        <td data-field="nombre"><input type="text" required></td>
        <td data-field="precio"><input type="number" min="0" step="0.01" required></td>
        <td data-field="cantidad"><input type="number" min="0" required></td>
        <td data-field="fecha"><input type="date" required max="${getCurrentDate()}"></td>
        <td data-field="descripcion"><input type="text"></td>
        <td data-field="codigo"><input type="number", min="0"></td>
        <td>
            <button onclick="createProducto()">Crear</button>
            <button onclick="cancelCreate()">Cancelar</button>
        </td>
    `;

    // Insertar la nueva fila al principio del cuerpo de la tabla
    tableBody.insertBefore(newRow, tableBody.firstChild);
}

/**
 * Muestra un mensaje en la pantalla con un tipo específico (error o éxito).
 * 
 * @description Esta función busca un contenedor con el id "message" y muestra el mensaje proporcionado
 *              con un estilo adecuado según el tipo de mensaje (error o éxito). El mensaje se muestra
 *              durante 5 segundos y luego se oculta con un efecto de fade out.
 * 
 * @param {string} message - El texto del mensaje a mostrar
 * @param {string} type - El tipo de mensaje (error o success)
 * 
 * @example
 * showMessage('El producto se creó correctamente', 'success');
 * 
 * @returns {void}
 */
function showMessage(message, type) {
    // Buscar el contenedor de mensajes
    let container = document.getElementById('message');

    // Si el contenedor existe, mostrar el mensaje
    if (container != null) {
        // Establecer el texto del mensaje
        container.innerHTML = message;

        // Eliminar clases de mensajes anteriores
        container.classList.remove('error', 'success', 'fade-out');

        // Agregar clases para el tipo de mensaje actual
        container.classList.add('message');
        if (type === 'error') {
            // Agregar clase para mensaje de error
            container.classList.add('error');
        } else if (type === 'success') {
            // Agregar clase para mensaje de éxito
            container.classList.add('success');
        }

        // Agregar clase para mostrar el mensaje con un efecto de fade in
        container.classList.add('fade-in');

        // Ocultar el mensaje después de 5 segundos con un efecto de fade out
        setTimeout(() => {
            container.classList.replace('fade-in', 'fade-out');
        }, 5000); // Tiempo durante el cual el mensaje es visible
    } else {
        // Si no hay contenedor, mostrar el mensaje con un alert
        alert(message);
    }
}

/**
 * Cancela la edición de un producto.
 * 
 * @description Esta función recarga los datos de productos para cancelar la edición actual.
 * 
 * @example
 * cancelEdit();
 * 
 * @returns {void}
 */
function cancelEdit() {
    // Recargar datos de productos para cancelar la edición
    fetchProductos(currentPage, pageSize, sort);
}

/**
 * Cancela la creación de un nuevo producto.
 * 
 * @description Esta función elimina la fila de creación y muestra el botón de crear.
 * 
 * @example
 * cancelCreate();
 * 
 * @returns {void}
 */
function cancelCreate() {
    // Eliminar la fila de creación
    document.getElementById('createRow').remove();

    // Mostrar el botón de crear
    document.getElementById('createButton').style.display = 'inline-block';
}