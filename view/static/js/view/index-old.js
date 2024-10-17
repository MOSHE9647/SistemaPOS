import { mostrarMensaje } from "../gui/notification.js";
import { eliminarCSS, importarCSS } from "../utils.js";
import { cargarRoles } from "./rol/main.js";

const vistas = {
    roles: {
        css: './view/static/css/view/rol.css',
        script: cargarRoles,
    }
};

// Función para cargar la vista de CRUD
export function cargarCRUD() {
    // Selecciona todos los enlaces con la clase 'link'
    const links = document.querySelectorAll('.link');

    // Asigna un evento click a cada enlace
    links.forEach(link => {
        link.addEventListener('click', async (event) => {
            event.preventDefault(); // Previene la acción por defecto de abrir el enlace
            const url = link.getAttribute('href'); // Obtiene el valor del atributo 'href' del enlace
            const keyword = url.split('/').pop().split('.').shift(); // Obtiene la palabra clave del enlace

            // Verifica si la palabra clave existe en el objeto vistas
            if (vistas[keyword]) {
                try {
                    eliminarCSS(); // Elimina los estilos CSS adicionales
                    const vista = vistas[keyword]; // Obtiene la información de la vista correspondiente

                    // Carga el contenido HTML desde la URL del enlace
                    const response = await fetch(url);
                    if (!response.ok) {
                        mostrarMensaje(`Error HTTP! estado: ${response.status}`, 'error', 'Error interno');
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const html = await response.text();
                    importarCSS(vista.css); // Inserta el archivo CSS de la vista

                    // Obtiene el contenedor principal y reemplaza su contenido
                    const container = document.querySelector('main');
                    container.innerHTML = html;

                    // Ejecuta el script correspondiente
                    vista.script();
                } catch (error) {
                    mostrarMensaje(`Ocurrió un error al cargar la vista.<br> ${error.message}`, 'error', 'Error interno');
                }
            }
        });
    });
}
