// Función para eliminar múltiples parámetros de la URL
export function removeUrlParams(params) {
    const url = new URL(window.location);
    params.forEach(param => url.searchParams.delete(param)); // Elimina cada parámetro
    window.history.replaceState({}, '', url); // Reemplaza la URL sin los parámetros
}

// Función para interpretar mensajes de error del servidor
export function interpretarMensaje(status, serverMessage) {
    switch(status) {
        case 404:
            return 'Recurso no encontrado. La URL solicitada no existe.';
        case 500:
            return 'Error interno del servidor. Intente más tarde.';
        case 503:
            return 'Servicio no disponible. El servidor está temporalmente fuera de servicio.';
        default:
            return serverMessage || 'Ocurrió un error inesperado. Intente más tarde.';
    }
}

// Función para importar un archivo CSS
export function importarCSS(filePath) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = filePath;
    document.head.appendChild(link);
}

// Función para eliminar archivos CSS adicionales
export function eliminarCSS() {
    const links = document.querySelectorAll('link[rel="stylesheet"]');
    links.forEach(link => {
        if (!link.href.includes('index.css')) {
            link.remove();
        }
    });
}

export function checkEmptyTable(tableBodyID, iconClass) {
    const tableBody = document.getElementById(tableBodyID);
    const tableHeader = document.querySelector("table thead tr");

    // Verifica si el tbody está vacío
    if (tableBody.rows.length === 0) {
        // Crear una fila
        const row = document.createElement("tr");

        // Obtener la cantidad de columnas desde el thead
        const columnCount = tableHeader.children.length;

        // Crear una celda que ocupará todas las columnas
        const cell = document.createElement("td");
        cell.colSpan = columnCount; // Establecer el colSpan dinámicamente
        cell.classList.add("nodata"); // Añadir la clase nodata

        // Añadir el icono y el mensaje
        const icon = document.createElement("i");
        icon.className = iconClass; // Usar la clase del icono proporcionada
        const message = document.createElement("p");
        message.textContent = "No hay registros disponibles";

        // Añadir el icono y el mensaje a la celda
        cell.appendChild(icon);
        cell.appendChild(message);

        // Añadir la celda a la fila
        row.appendChild(cell);

        // Añadir la fila al cuerpo de la tabla
        tableBody.appendChild(row);
    }
}

export function getCurrentDate() {
    let today = new Date();
    let year = today.getFullYear();
    let month = (today.getMonth() + 1).toString().padStart(2, '0');
    let day = today.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}

export function formatearTelefono(codigoPais, numeroTelefono) {
    numeroTelefono = numeroTelefono.replace(/\D/g, ''); // Eliminar caracteres no numéricos
    let numeroFormateado = '';

    switch (codigoPais) {
        case '+54': // Argentina
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+591': // Bolivia
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+56': // Chile
            numeroFormateado = numeroTelefono.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3');
            break;
        case '+57': // Colombia
            numeroFormateado = numeroTelefono.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
            break;
        case '+506': // Costa Rica
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+53': // Cuba
            numeroFormateado = numeroTelefono.replace(/(\d{3})(\d{4})(\d{4})/, '$1 $2 $3');
            break;
        case '+593': // Ecuador
            numeroFormateado = numeroTelefono.replace(/(\d{2})(\d{3})(\d{4})/, '$1 $2 $3');
            break;
        case '+503': // El Salvador
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+502': // Guatemala
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+504': // Honduras
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+52': // México
            numeroFormateado = numeroTelefono.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3');
            break;
        case '+505': // Nicaragua
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+507': // Panamá
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+595': // Paraguay
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+51': // Perú
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        case '+1-809': // República Dominicana
            numeroFormateado = numeroTelefono.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
            break;
        case '+598': // Uruguay
            numeroFormateado = numeroTelefono.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3');
            break;
        case '+58': // Venezuela
            numeroFormateado = numeroTelefono.replace(/(\d{4})(\d{4})/, '$1 $2');
            break;
        default:
            numeroFormateado = numeroTelefono; // Sin formato específico
            break;
    }

    return numeroFormateado;
}

export function manejarInputNumeroTelefono() {
    const codigoPais = document.getElementById('codigo-select').value;
    if (!codigoPais) return;
    let numeroTelefono = document.getElementById('numero').value;
    numeroTelefono = formatearTelefono(codigoPais, numeroTelefono);
    document.getElementById('numero').value = numeroTelefono;
}