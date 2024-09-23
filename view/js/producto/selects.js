document.addEventListener("DOMContentLoaded", function() {
    // Cargar las categorías y subcategorías cuando se cargue la página
    loadCategoriasSubcategorias();
});
function fillCategoriasCombo(categorias) {
    let categoriaSelect = document.getElementById('categoria-select');
    categoriaSelect.innerHTML = '<option value="">-- Seleccionar categoría --</option>'; // Limpiar el combo box

    categorias.forEach(categoria => {
        let option = document.createElement('option');
        option.value = categoria.id;
        option.textContent = categoria.nombre;
        categoriaSelect.appendChild(option);
    });
}

async function loadCategoriasSubcategorias() {
    try {
        const response = await fetch('controller/listarSubcategorias.php?accion=listarSubcategorias');
        if (!response.ok) {
            throw new Error('Error al cargar las categorías');
        }
        const data = await response.json();

        // Cargar las categorías en el combo box
        const categoriaSelect = document.getElementById('categoria-select');
        categoriaSelect.innerHTML = '<option value="">-- Seleccionar categoría --</option>'; // Limpiar el combo box de categorías
        data.categorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.id;
            option.textContent = categoria.nombre;
            categoriaSelect.appendChild(option);
        });

        // Event listener para actualizar las subcategorías según la categoría seleccionada
        categoriaSelect.addEventListener('change', function() {
            const selectedCategoriaId = parseInt(categoriaSelect.value);
            const subcategoriaSelect = document.getElementById('subcategoria-select');
            subcategoriaSelect.innerHTML = '<option value="">-- Seleccionar subcategoría --</option>'; // Limpiar el combo box de subcategorías

            const categoriaSeleccionada = data.categorias.find(categoria => categoria.id === selectedCategoriaId);
            if (categoriaSeleccionada && categoriaSeleccionada.subcategorias) {
                categoriaSeleccionada.subcategorias.forEach(subcategoria => {
                    const option = document.createElement('option');
                    option.value = subcategoria.id;
                    option.textContent = subcategoria.nombre;
                    subcategoriaSelect.appendChild(option);
                });
            }
        });
    } catch (error) {
        console.error(error);
        alert('No se pudieron cargar las categorías y subcategorías. Intenta de nuevo más tarde.');
    }

    
}
