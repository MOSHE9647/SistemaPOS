import { mostrarMensaje } from '../js/gui/notification.js';
import { showLoader, hideLoader } from '../js/gui/loader.js';
import { eliminarCSS, importarCSS } from '../js/utils.js';
import { cargarClientes } from '../js/view/clientes.js';

// Función para ejecutar funciones de la vista
function ejecutarFunciones(vista) {
    if (vista !== 'home') {
        let basePath = '../front/view/static/css/';
        eliminarCSS();
        switch (vista) {
            case 'clientes':
                cargarClientes();
                importarCSS(basePath + 'view/cliente.css');
                break;
            default:
                mostrarMensaje('No se encontró la vista solicitada. ' + vista, 'error', 'Error interno');
                break;
        }
    }
}

// Función para cargar la vista
async function cargarVista(url, contID) {
    try {
        // Mostrar loader
        showLoader();
        // Cargar vista
        const response = await fetch(url + '&ajax=true');
        // Ocultar loader
        hideLoader();

        // Limpiar contenedor
        const contenedor = document.querySelector(contID);
        contenedor.innerHTML = '';

        // Verificar respuesta
        if (response.ok) {
            // Verificar si la respuesta es un archivo HTML
            const text = await response.text();
            if (text) {
                contenedor.innerHTML = text; // Cargar vista
                const vista = url.split('=')[1]; // Obtener nombre de la vista
                ejecutarFunciones(vista); // Ejecutar funciones de la vista
            }
            else mostrarMensaje('No se encontraron datos para cargar la página.', 'error', 'Error interno');
        } else {
            mostrarMensaje(`Ocurrió un error al cargar la vista. Error ${response.status}: ${response.statusText}`, 'error', 'Error interno');
        }
    } catch (error) {
        hideLoader();
        mostrarMensaje(`Ocurrió un error al cargar la vista. ${error}`, 'error', 'Error interno');
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const enlaces = document.querySelectorAll(".sidemenu a, #config-link");
    enlaces.forEach(enlace => {
        enlace.addEventListener("click", event => {
            event.preventDefault();
            enlaces.forEach(element => element.classList.remove('active'));
            enlace.classList.add('active');
            const url = enlace.getAttribute("href");
            cargarVista(url, 'main');
        });
    });

    var productos = document.querySelectorAll(".product-card");
    productos.forEach(producto => {
        producto.addEventListener("click", () => {
            mostrarMensaje('Producto seleccionado', 'success', 'Producto');
        });
    });
});