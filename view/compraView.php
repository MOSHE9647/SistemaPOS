<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Compras | POSFusion</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <h2>Lista de Compras</h2>

    <div id="message"></div>

    <div class="table-container">
        <div class="table-header">
            <!-- Botón para crear nueva compra -->
            <button id="createButton" onclick="showCreateRow()">Crear Compra</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th data-field="compranumerofactura">Número de Factura</th>
                    <th data-field="proveedornombre">Proveedor</th>
                    <th data-field="compramontobruto">Monto Bruto</th>
                    <th data-field="compramontoneto">Monto Neto</th>
                    <th data-field="compratipopago">Tipo de Pago</th>
                    <th data-field="comprafechacreacion">Fecha de Creación</th>
                    <th data-field="comprafechamodificacion">Fecha de Modificación</th>
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
    
    <!-- Scripts de JavaScript -->
    <script src="./js/utils.js"></script>                   <!-- Utiles para mostrar notificaciones y demás           -->
    <script src="./js/compra/compraGUI.js"></script>       <!-- Manejo dinámico de la página                         -->
    <script src="./js/compra/compraPagination.js"></script><!-- Métodos para Paginación                              -->
    <script src="./js/compra/compraCrud.js"></script>      <!-- Creación, Actualización y Eliminación de Compras     -->
</body>
</html>
