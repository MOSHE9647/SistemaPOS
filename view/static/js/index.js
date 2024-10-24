import { mostrarMensaje } from './gui/notification.js';
import { hideLoader, showLoader } from './gui/loader.js';
import { eliminarCSS, importarCSS } from './utils.js';
import { cargarClientes } from './view/cliente/main.js';
import { cargarUsuarios } from './view/usuario/main.js';
import { cargarProductos } from './view/producto/main.js';
import { cargarProveedores } from './view/proveedor/main.js';
import { cargarCRUD } from './view/index-old.js';

// Ruta base para las peticiones fetch y otros recursos
window.baseURL = window.location.pathname.split('/').slice(0, -1).join('/');

// Rutas y funciones asociadas a las vistas
const vistas = {
    home: { 
        css: './view/static/css/view/home.css' 
    },
    productos: { 
        css: './view/static/css/view/producto.css', 
        script: cargarProductos 
    },
    clientes: { 
        css: './view/static/css/view/cliente.css', 
        script: cargarClientes 
    },
    proveedores: { 
        css: './view/static/css/view/proveedor.css', 
        script: cargarProveedores 
    },
    usuarios: { 
        css: './view/static/css/view/usuario.css', 
        script: cargarUsuarios 
    },
    cruds: { 
        css: './view/static/css/index-old.css', 
        script: cargarCRUD 
    }
};

// Función para cargar estilos de la vista
function cargarEstilos(vista) {
    eliminarCSS(); // Eliminar los archivos CSS adicionales

    const configVista = vistas[vista];
    if (configVista) {
        importarCSS(configVista.css);
        document.title = vista === 'home' ? 'Inicio | POSFusion' : `${vista.charAt(0).toUpperCase() + vista.slice(1)} | POSFusion`;
    } else {
        mostrarMensaje(`No se encontró la vista solicitada: ${vista}`, 'error', 'Error interno');
    }
}

function cargarScripts(vista) {
    const configVista = vistas[vista];
    if (configVista?.script) {
        configVista.script();
    }
}

// Función para cargar la vista
async function cargarVista(url, contID) {
    showLoader();
    try {
        const contenedor = document.querySelector(contID);
        contenedor.innerHTML = '';

        const response = await fetch(`${url}&ajax=true`);
        if (!response.ok) throw new Error(`Error ${response.status}: ${response.statusText}`);

        const vista = url.split('=')[1];
        const text = await response.text();

        if (text) {
            contenedor.innerHTML = text;
            cargarScripts(vista);
        } else {
            mostrarMensaje('No se encontraron datos para cargar la página.', 'error', 'Error interno');
        }
    } catch (error) {
        mostrarMensaje(`Ocurrió un error al cargar la vista. ${error.message}`, 'error', 'Error interno');
    } finally {
        if (url.split('=')[1] === 'home' || 'cruds') hideLoader();
    }
}

// Delegación de eventos para los enlaces
document.addEventListener("DOMContentLoaded", () => {
    document.body.addEventListener("click", event => {
        // Evento para cargar las vistas
        const enlace = event.target.closest(".sidemenu a, #config-link");
        if (enlace) {
            // Evitar la recarga de la página
            event.preventDefault();

            // Cambiar el estado activo del enlace
            document.querySelectorAll(".sidemenu a, #config-link").forEach(link => link.classList.remove('active'));
            enlace.classList.add('active');

            // Cargar la vista solicitada y sus estilos
            const url = enlace.getAttribute("href");
            const vista = url.split('=')[1];
            cargarEstilos(vista);
            cargarVista(url, 'main');
        }
    });
});
