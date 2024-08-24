<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Lotes | POSFusion</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <h2>Lista de Lotes</h2>

    <div id="message"></div>

    <div class="table-container">
        <div class="table-header">
            <!-- Botón para crear nuevo lote -->
            <button id="createButton" onclick="showCreateRow()">Crear</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th data-field="lotecodigo">Código del Lote</th>
                    <th data-field="compraid">Compra</th>
                    <th data-field="productonombre">Nombre del Producto</th>
                    <th data-field="proveedornombre">Nombre del Proveedor</th>
                    <th data-field="lotefechavencimiento">Fecha de Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Las filas se llenan dinámicamente con JavaScript -->
            </tbody>
        </table>

        <div class="pagination-container">
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
            <div id="paginationControls">
                <button id="prevPage" onclick="changePage(currentPage - 1)">Anterior</button>
                <span id="pageInfo">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
                <button id="nextPage" onclick="changePage(currentPage + 1)">Siguiente</button>
            </div>
        </div>
    </div>

    <a href="../index.php" class="menu-button">Regresar al Menú</a>
    <script src="./js/lote/lotesGUI.js"></script>           <!-- Manejo dinámico de la página                         -->
    <script src="./js/lote/lotesPagination.js"></script>    <!-- Métodos para Paginación                              -->
    <script src="./js/lote/lotesCrud.js"></script>          <!-- Creación, Actualización y Eliminación de Lotes      -->
</body>
</html>
