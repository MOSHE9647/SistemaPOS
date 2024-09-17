<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cuentas por Pagar | POSFusion</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <h2>Lista de Cuentas por Pagar</h2>

    <div id="message"></div>

    <div class="table-container">
        <div class="table-header">
            <!-- Botón para crear nueva cuenta por pagar -->
            <!--<button id="createButton" onclick="showCreateRow()">Crear</button> -->
        </div>

        <table>
            <thead>
                <tr>
                    <th data-field="compradetalleid">Compra Detalle</th>
                    <th data-field="fechavencimiento">Fecha de Vencimiento</th>
                    <th data-field="montototal">Monto Total</th>
                    <th data-field="montopagado">Monto Pagado</th>
                    <th data-field="fechapago">Fecha de Pago</th>
                    <th data-field="notas">Notas</th>
                    <th data-field="estadocuenta">Estado Cuenta</th>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Archivos JavaScript para manejar la lógica de cuentas por pagar -->
    <script src="./js/utils.js"></script>                   <!-- Utilidades para mostrar notificaciones y demás -->
    <script src="./js/cuentaPorPagar/cuentaporpagarGUI.js"></script>   <!-- Manejo dinámico de la página -->
    <script src="./js/cuentaPorPagar/cuentaporpagarPagination.js"></script> <!-- Métodos para Paginación -->
    <script src="./js/cuentaPorPagar/cuentaporpagarCrud.js"></script>  <!-- Creación, Actualización y Eliminación de Cuentas por Pagar -->
</body>
</html>
