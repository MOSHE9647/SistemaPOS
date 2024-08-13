<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Impuestos | SistemaPOS</title>
        <?php 
            include __DIR__ . '/../service/impuestoBusiness.php'; 
            require_once __DIR__ . '/../utils/Utils.php';
        ?>
        <link rel="stylesheet" href="./css/styles.css">
    </head>
    <body>

        <h2>Lista de Impuestos</h2>

        <div id="message"></div>

        <!-- Botón para crear nuevo impuesto -->
        <button id="createButton" onclick="showCreateRow()">Crear</button>

        <table>
            <thead>
                <tr>
                    <th data-field="nombre">Nombre</th>
                    <th data-field="valor">Valor</th>
                    <th data-field="descripcion">Descripción</th>
                    <th data-field="fecha_vigencia">Fecha Vigencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Las filas se llenarán con JavaScript -->
            </tbody>
        </table>

        <!-- Controles de paginación -->
        <div id="paginationControls">
            <button id="prevPage" onclick="changePage(currentPage - 1)">Anterior</button>
            <span> Página <span id="currentPage">1</span> de <span id="totalPages">1</span> </span>
            <button id="nextPage" onclick="changePage(currentPage + 1)">Siguiente</button>
        </div>

        <!-- Selector de tamaño de página -->
        <div id="pageSizeSelector">
            <label for="pageSize">Tamaño de página:</label>
            <select id="pageSize">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
            </select>
        </div>

        <a href="../index.php" class="menu-button">Regresar al Menú</a>
        <script src="./js/impuesto.js"></script>
    </body>
</html>
