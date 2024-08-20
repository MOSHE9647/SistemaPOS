// *********************************************************************************************** //
// ************* Métodos para obtener las listas de Provincias, Cantones y Distritos ************* //
// *********************************************************************************************** //

/**
 * Carga datos de un archivo JSON y devuelve un objeto JSON.
 * 
 * La función carga los datos de un archivo ubicado en '../view/js/direccion/datos.json'
 * y devuelve el objeto JSON parseado. Si ocurre un error durante la carga o parsing,
 * captura el error, lo registra en la consola y devuelve un objeto vacío.
 * 
 * @returns {object} El objeto JSON parseado del archivo.
 * 
 * @example
 * const datos = await loadSelects();
 * console.log(datos); // Salida: { provincias: [...], cantones: [...] }
 */
async function loadSelects() {
    try {
        const response = await fetch('../view/js/direccion/datos.json');
        return await response.json();
    } catch (error) {
        console.error('Error cargando datos JSON:', error);
        showMessage('Error cargando los datos de las listas:', error);
        return {};
    }
}

/**
 * Inicializa los selects de provincia, cantón y distrito.
 * 
 * Carga los datos de las provincias, cantones y distritos desde un archivo JSON
 * y asigna los datos a una variable global. Luego, añade event listeners para
 * actualizar los selects dependientes cuando se selecciona una provincia o cantón.
 * 
 * @example
 * initializeSelects();
 */
function initializeSelects() {
    loadSelects().then(data => {
        // Asignar los datos a una variable global
        window.data = data;
        loadProvincias();
    });

    // Añadir event listeners para actualizar los selects dependientes
    document.getElementById('provincia-select').addEventListener('change', loadCantones);
    document.getElementById('canton-select').addEventListener('change', loadDistritos);
}

/**
 * Carga las provincias en el select de provincias.
 * 
 * Limpia las opciones anteriores y carga las provincias desde la variable global `window.data`.
 * 
 * @example
 * loadProvincias();
 */
function loadProvincias() {
    const provinciaSelect = document.getElementById('provincia-select');
    let value = provinciaSelect.value;
    provinciaSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    // Asegura que `window.data` esté disponible antes de usarlo
    if (window.data.provincias) {
        window.data.provincias.forEach(provincia => {
            const option = document.createElement('option');
            option.dataset.field = provincia.id;
            option.value = provincia.nombre;
            option.textContent = provincia.nombre;
            option.selected = option.value === value;
            provinciaSelect.appendChild(option);
        });
    }

    if (value !== null) {
        loadCantones();
    }
}

/**
 * Carga los cantones en el select de cantones según la provincia seleccionada.
 * 
 * Limpia las opciones anteriores y carga los cantones desde la variable global `window.data` según la provincia seleccionada.
 * 
 * @example
 * loadCantones();
 */
function loadCantones() {
    const cantonSelect = document.getElementById('canton-select');
    let value = cantonSelect.value;
    cantonSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores
    
    const provinciaSelect = document.getElementById('provincia-select');
    const provinciaIndex = (provinciaSelect.options[provinciaSelect.selectedIndex].dataset.field) - 1;

    if (provinciaIndex >= 0 && window.data.provincias[provinciaIndex]) {
        window.data.provincias[provinciaIndex].cantones.forEach(canton => {
            const option = document.createElement('option');
            option.dataset.field = canton.id;
            option.value = canton.nombre;
            option.textContent = canton.nombre;
            option.selected = option.value === value;
            cantonSelect.appendChild(option);
        });
    }

    if (value !== null) {
        loadDistritos();
    }
}

/**
 * Carga los distritos en el select de distritos según la provincia y cantón seleccionados.
 * 
 * Limpia las opciones anteriores y carga los distritos desde la variable global `window.data` según la provincia y cantón seleccionados.
 * 
 * @example
 * loadDistritos();
 */
function loadDistritos() {
    const distritoSelect = document.getElementById('distrito-select');
    let value = distritoSelect.value;
    distritoSelect.innerHTML = '<option value="">-- Seleccionar --</option>'; // Limpiar opciones anteriores

    const provinciaSelect = document.getElementById('provincia-select');
    const provinciaIndex = (provinciaSelect.options[provinciaSelect.selectedIndex].dataset.field) - 1;

    const cantonSelect = document.getElementById('canton-select');
    const cantonIndex = (cantonSelect.options[cantonSelect.selectedIndex].dataset.field) - 1;

    if (cantonIndex >= 0 && provinciaIndex >= 0 && window.data.provincias[provinciaIndex].cantones[cantonIndex]) {
        window.data.provincias[provinciaIndex].cantones[cantonIndex].distritos.forEach(distrito => {
            const option = document.createElement('option');
            option.value = distrito.nombre;
            option.textContent = distrito.nombre;
            option.selected = option.value === value;
            distritoSelect.appendChild(option);
        });
    }
}