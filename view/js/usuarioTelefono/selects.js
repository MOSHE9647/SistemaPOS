// *************************************************************************************************************** //
// ************* Métodos para obtener las listas de Usuarios, Tipos de Teléfono y Códigos de País ************* //
// *************************************************************************************************************** //

/**
 * Carga la lista de usuarios desde el servidor.
 * 
 * @returns {Promise<Object>} Una promesa que se resuelve con la lista de usuarios.
 * 
 * @example
 * loadUsuarios().then(data => {
 *   console.log(data);
 * });
 */
async function loadUsuarios() {
    try {
        // Enviar la solicitud GET al servidor para obtener los usuarios con los datos en la URL
        const response = await fetch('../controller/usuarioAction.php?accion=todo');
        return await response.json();
    } catch (error) {
        // Muestra el mensaje de error detallado
        showMessage(`Ocurrió un error al obtener la lista de usuarios.<br>${error}`, 'error');
        return {};
    }
}

loadUsuarios().then(data => {
    // Verificar si la respuesta fue exitosa
    if (data.success) {
        // Asignar los datos a una variable global
        window.dataU = data;
    } else {
        // Muestra el mensaje de error específico enviado desde el servidor
        showMessage(data.message, 'error');
        window.dataU = {};
    }
    loadSelectUsuarios();
});

/**
 * Carga las opciones del select de usuarios.
 */
function loadSelectUsuarios() {
    const usuariosSelect = document.getElementById('usuario-select');
    let value = usuariosSelect.value;
    usuariosSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    // Asegura que `window.dataU` esté disponible antes de usarlo
    if (window.dataU.usuarios) {
        window.dataU.usuarios.forEach(usuario => {
            const option = document.createElement('option');
            option.value = usuario.ID;
            option.textContent = usuario.Nombre + ' ' + usuario.Apellido1 + ' ' + usuario.Apellido2;
            option.selected = option.value === value;
            usuariosSelect.appendChild(option);
        });
    }
}