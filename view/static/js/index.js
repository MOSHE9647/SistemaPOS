import { mostrarMensaje } from './gui/notification.js';
import { showLoader, hideLoader } from './gui/loader.js';
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
        css: './view/static/css/view/home.css',
    },
    productos: {
        css: './view/static/css/view/producto.css',
        script: cargarProductos,
    },
    clientes: {
        css: './view/static/css/view/cliente.css',
        script: cargarClientes,
    },
    proveedores: {
        css: './view/static/css/view/proveedor.css',
        script: cargarProveedores,
    },
    usuarios: {
        css: './view/static/css/view/usuario.css',
        script: cargarUsuarios,
    },
    cruds: {
        css: './view/static/css/index-old.css',
        script: cargarCRUD,
    },
};

// Función para cargar estilos de la vista
function cargarEstilos(vista) {
    eliminarCSS(); // Eliminar los archivos CSS adicionales

    const configVista = vistas[vista];
    if (configVista) {
        importarCSS(configVista.css);

        // Cambiar el título de la ventana
        document.title = vista === 'home' ? 'Inicio | POSFusion' : `${vista.charAt(0).toUpperCase() + vista.slice(1)} | POSFusion`;
    } else {
        mostrarMensaje('No se encontró la vista solicitada: ' + vista, 'error', 'Error interno');
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
        // Limpiar el contenedor
        const contenedor = document.querySelector(contID); // Obtiene el contenedor
        contenedor.innerHTML = ''; //<- Limpia el contenedor

        // Realizar la petición fetch
        const response = await fetch(`${url}&ajax=true`);
        if (!response.ok) throw new Error(`Error ${response.status}: ${response.statusText}`);

        // Obtener el nombre de la vista y su contenido
        const vista = url.split('=')[1]; // Obtener el nombre de la vista
        const text = await response.text(); // Obtener el contenido de la vista

        // Cargar la vista en el contenedor
        if (text) {
            contenedor.innerHTML = text; // Reemplaza el contenido del contenedor
            cargarScripts(vista); // Cargar los scripts de la vista
        } else {
            mostrarMensaje('No se encontraron datos para cargar la página.', 'error', 'Error interno');
        }
    } catch (error) {
        mostrarMensaje(`Ocurrió un error al cargar la vista. ${error.message}`, 'error', 'Error interno');
    } finally {
        hideLoader();
    }
}

// Delegación de eventos para los enlaces
document.addEventListener("DOMContentLoaded", () => {
    document.body.addEventListener("click", event => {
        const enlace = event.target.closest(".sidemenu a, #config-link");
        if (enlace) {
            event.preventDefault(); // Prevenir la acción por defecto
            document.querySelectorAll(".sidemenu a, #config-link")
                .forEach(link => link.classList.remove('active')); // Desmarcar todos los enlaces
            enlace.classList.add('active'); // Marcar el enlace como activo
            const url = enlace.getAttribute("href"); // Obtener la URL del enlace
            const vista = url.split('=')[1]; // Obtener el nombre de la vista
            cargarEstilos(vista); // Cargar los estilos de la vista
            cargarVista(url, 'main');
        }
    });
});