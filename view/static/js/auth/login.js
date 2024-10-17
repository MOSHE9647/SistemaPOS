import { mostrarMensaje } from '../../js/gui/notification.js';
import { showLoader, hideLoader } from '../../js/gui/loader.js';
import { removeUrlParams, interpretarMensaje } from '../utils.js';

// Exporta las funciones para poder ser utilizadas desde el archivo HTML
window.mostrarMensaje = mostrarMensaje;
window.removeUrlParams = removeUrlParams;

// Agrega un evento para enviar el formulario de inicio de sesión
document.getElementById('loginForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    await iniciarSesion();
});

// Función para enviar los datos del formulario de inicio de sesión
async function iniciarSesion() {
    showLoader(); // Muestra el loader

    const form = document.getElementById('loginForm');
    const formData = new FormData(form);
    const url = form.getAttribute('action');

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(interpretarMensaje(response.status, errorData.message));
        }

        const data = await response.json();
        hideLoader(); // Oculta el loader

        if (data.success) {
            location.href = data.redirect;
        } else {
            mostrarMensaje(data.message, 'error', 'Error al intentar iniciar sesión');
        }
    } catch (error) {
        hideLoader(); // Oculta el loader
        mostrarMensaje(error.message, 'error', 'Error al intentar iniciar sesión');
    }
}
