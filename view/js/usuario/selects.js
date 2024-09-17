// ********************************************************************************** //
// ************* Métodos para obtener la lista de roles para un usuario ************* //
// ********************************************************************************** //

async function loadRoles() {
    try {
        // Enviar la solicitud GET al servidor para obtener los roles con los datos en la URL
        const response = await fetch('../controller/rolUsuarioAction.php?accion=todos');
        return await response.json();
    } catch (error) {
        showMessage(`Ocurrió un error al obtener la lista de roles.<br>${error}`, 'error');
        return {};
    }
}

function initializeSelects() {
    loadRoles().then(data => {
        // Asignar los datos a una variable global
        window.dataR = data;
        loadSelectRoles();
    });
}

function loadSelectRoles() {
    const rolesSelect = document.getElementById('rol-select');
    let value = rolesSelect.value;
    rolesSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    if (window.dataR.roles) {
        window.dataR.roles.forEach(rol => {
            const option = document.createElement('option');
            option.value = rol.ID;
            option.textContent = rol.Nombre;
            option.selected = option.value === value;
            rolesSelect.appendChild(option);
        });
    }
}