<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de Compras de Productos | POSFusion</title>
        <link rel="stylesheet" href="./css/styles.css">
    </head>
    <body>
        <!-- Título de la página -->
        <h2>Lista de Compras de Productos</h2>

        <!-- Contenedor para mensajes de notificación -->
        <div id="message"></div>

        <!-- Contenedor principal de la tabla -->
        <div class="table-container">
            <div class="table-header">
                <!-- Botón para crear una nueva compra de producto -->
                <button id="createButton" onclick="showCreateRow()">Crear Nueva Compra</button>
            </div>

            <!-- Tabla para mostrar las compras de productos -->
            <table>
                <thead>
                    <tr>
                        <th data-field="compraproductoid">ID de Compra</th>
                        <th data-field="compraproductocantidad">Cantidad</th>
                        <th data-field="proveedornombre">Nombre del Proveedor</th>
                        <th data-field="compraproductofechacreacion">Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Las filas se llenarán dinámicamente con JavaScript -->
                </tbody>
            </table>

            <!-- Controles de paginación -->
            <div class="pagination-container">
                <div id="paginationSize">
                    Mostrando:
                    <select id="pageSizeSelector" onchange="changePageSize()">
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

        <!-- Botón para regresar al menú principal -->
        <a href="../index.php" class="menu-button">Regresar al Menú</a>

        <!-- Enlace a los scripts de JavaScript -->
        <script src="./js/utils.js"></script>                    <!-- Utilidades para mensajes y otras funciones -->
        <script src="./js/Compra/CompraProductoGui.js"></script> <!-- Manejo dinámico de la interfaz de usuario -->
        <script src="./js/Compra/CompraProductoCrud.js"></script> <!-- Lógica para operaciones CRUD -->
    </body>
</html>
