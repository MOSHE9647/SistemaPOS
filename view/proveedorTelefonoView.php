<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Teléfonos de Proveedores | SistemaPOS</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>

    <h2>Lista de Teléfonos de Proveedores</h2>

    <div id="message"></div>

    <!-- Botón para crear nuevo teléfono -->
    <button id="createButton" onclick="showCreateRow()">Crear</button>

    <table>
        <thead>
            <tr>
                <th data-field="proveedorid">Proveedor ID</th>
                <th data-field="proveedor">Nombre Proveedor</th>
                <th data-field="telefono">Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <!-- Las filas se llenan dinámicamente con JavaScript -->
        </tbody>
    </table>

    <div class="pagination-container">
        <!-- Selector de tamaño de página -->
        <div id="paginationSize">
            Mostrando:
            <select id="pageSizeSelector">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            de <span id="totalRecords"></span> registros
        </div>

        <!-- Controles de paginación -->
        <div id="paginationControls">
            <button id="prevPage" onclick="changePage(currentPage - 1)">Anterior</button>
            <span id="pageInfo">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
            <button id="nextPage" onclick="changePage(currentPage + 1)">Siguiente</button>
        </div>
    </div>

    <a href="../index.php" class="menu-button">Regresar al Menú</a>
    <script src="./js/proveedorTelefono.js"></script>
</body>
</html>
