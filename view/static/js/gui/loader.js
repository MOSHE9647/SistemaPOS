// Muestra el loader
export function showLoader() {
	const loader = document.getElementById("loader");
	if (!loader) {
		console.error("Loader element not found");
		return;
	}
	loader.style.display = "flex";
}

// Oculta el loader
export function hideLoader() {
	const loader = document.getElementById("loader");
	if (!loader) {
		console.error("Loader element not found");
		return;
	}
	loader.style.display = "none";
}

export function showTableLoader(tableBodyID, loaderText) {
    const tableBody = document.getElementById(tableBodyID);
    const tableHeader = document.querySelector("table thead tr");

	// Vaciar el tbody y crear una fila
	tableBody.innerHTML = "";
	const row = document.createElement("tr");

	// Obtener la cantidad de columnas desde el thead
	const columnCount = tableHeader.children.length;

	// Crear una celda que ocupará todas las columnas
	const cell = document.createElement("td");
	cell.colSpan = columnCount; // Establecer el colSpan dinámicamente
	cell.classList.add("nodata"); // Añadir la clase de nodata para centrar el texto

	// Añadir el icono y el mensaje de carga
	const icon = document.createElement("i");
	icon.className = "la la-spinner la-spin"; // Usar el icono de carga
	const message = document.createElement("p");
	message.textContent = loaderText; // Usar el texto de carga proporcionado

	// Añadir el icono y el mensaje a la celda
	cell.appendChild(icon);
	cell.appendChild(message);

	// Añadir la celda a la fila y la fila al cuerpo de la tabla
	row.appendChild(cell);
	tableBody.appendChild(row);
}