<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos | POSFusion</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <h2>Lista de Productos</h2>

    <div id="message"></div>

    <div class="table-container">
        <div class="table-header">
            <!-- Botón para crear nuevo producto -->
            <button id="createButton" onclick="showCreateRow()">Crear</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th data-field="nombre">Nombre</th>
                    <th data-field="precioCompra">Precio de Compra</th>
                    <th data-field="ganancia">Porcentaje de Ganancia</th>
                    <th data-field="descripcion">Descripción</th>
                    <th data-field="codigoBarrasID">Código de Barras</th>
                    <th data-field="foto">Imagen</th>
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

    <!-- Archivos JavaScript para manejar la lógica de productos -->
    <script src="./js/utils.js"></script>                   <!-- Utilidades para mostrar notificaciones y demás -->
    <script src="./js/producto/productosGUI.js"></script>   <!-- Manejo dinámico de la página -->
    <script src="./js/producto/productosPagination.js"></script> <!-- Métodos para Paginación -->
    <script src="./js/producto/productosCrud.js"></script>  <!-- Creación, Actualización y Eliminación de Productos -->
</body>
</html>
