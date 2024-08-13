<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos | SistemaPOS</title>
    <?php 
        include __DIR__ . '/../service/productoBusiness.php'; 
        require_once __DIR__ . '/../utils/Utils.php';
    ?>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>

    <h2>Lista de Productos</h2>

    <div id="message"></div>

    <!-- Botón para crear nuevo producto -->
    <button id="createButton" onclick="showCreateRow()">Crear</button>

    <table>
        <thead>
            <tr>
                <th data-field="nombreproducto">Nombre</th>
                <th data-field="preciounitarioproducto">Precio Unitario</th>
                <th data-field="cantidadproducto">Cantidad</th>
                <th data-field="fechaadquisicionproducto">Fecha Adquisición</th>
                <th data-field="descripcionproducto">Descripción</th>
                <th data-field="estadoproducto">Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <!-- Las filas se cargarán dinámicamente con JavaScript -->
        </tbody>
    </table>

    <!-- Controles de paginación -->
    <div id="paginationControls">
        <button id="prevPage">Anterior</button>
        <span>Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
        <button id="nextPage">Siguiente</button>
        <label for="pageSize">Tamaño de página:</label>
        <select id="pageSize">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
        </select>
    </div>

    <a href="../index.php" class="menu-button">Regresar al Menú</a>

    <script src="./js/producto.js"></script>
</body>
</html>
