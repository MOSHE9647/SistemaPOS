/**
 * Muestra un mensaje al usuario.
 * 
 * @param {string} message - El texto del mensaje que se desea mostrar.
 * @param {string} type - El tipo de mensaje (error, success o info).
 * @description Muestra un mensaje en la pantalla con el texto y tipo especificados, y lo oculta después de unos segundos.
 * @example
 * showMessage('Dirección creada con éxito', 'success');
 */

function showMessage(message, type) {
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        if (type === 'success') {
            toastr.success(message);
        } else if (type === 'error') {
            toastr.error(message);
        } else if (type === 'info') {
            toastr.info(message);
        } else if (type === 'warning') {
            toastr.warning(message);
        }
    } else {
        console.error('Toastr no está definido.');
        alert(message); // Usa un alert como fallback si toastr no está disponible
    }
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

function formatearTelefono(codigoPais, numeroTelefono) {
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

function manejarInput() {
    const codigoPais = document.getElementById('codigo-select').value;
    let numeroTelefono = document.getElementById('numero').value;
    numeroTelefono = formatearTelefono(codigoPais, numeroTelefono);
    document.getElementById('numero').value = numeroTelefono;
}

function checkEmptyTable() {
    const tableBody = document.getElementById("tableBody");
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
        cell.textContent = "No hay registros disponibles";
        cell.style.textAlign = "center"; // Centrar el texto
        cell.style.height = "50px"; // Ajusta la altura según tus necesidades

        // Añadir la celda a la fila
        row.appendChild(cell);

        // Añadir la fila al cuerpo de la tabla
        tableBody.appendChild(row);
    }
}

function formatearDecimal(numero) {
    // Formatear el valor para que solo muestre decimales si es necesario
    let valorFormateado = parseFloat(numero);
    if (valorFormateado % 1 === 0) {
        valorFormateado = valorFormateado.toFixed(0);  // Si es entero, sin decimales
    } else {
        valorFormateado = valorFormateado.toFixed(2).replace(/\.?0+$/, '');  // Limitar a dos decimales y eliminar ceros innecesarios
    }
    return valorFormateado;
}