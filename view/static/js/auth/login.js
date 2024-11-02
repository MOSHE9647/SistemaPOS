import { mostrarMensaje } from '../../js/gui/notification.js';
import { showLoader, hideLoader } from '../../js/gui/loader.js';
import { removeUrlParams, interpretarMensaje, verificarRespuestaJSON } from '../utils.js';

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
        const response = await fetch(url, { method: 'POST', body: formData });
        if (!response.ok) {
            const errorData = await verificarRespuestaJSON(response);
            throw new Error(interpretarMensaje(response.status, errorData.message));
        }

        const data = await verificarRespuestaJSON(response);
        if (!data.success) {
            mostrarMensaje(data.message, 'error', 'Error al intentar iniciar sesión');
        }

        location.href = data.redirect;
    } catch (error) {
        mostrarMensaje(error.message, 'error', 'Error al intentar iniciar sesión');
    } finally {
        hideLoader(); // Oculta el loader
    }
}
