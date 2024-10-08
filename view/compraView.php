<!DOCTYPE html>
<html lang="es-cr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Compras | POSFusion</title>
    <link rel="stylesheet" href="./css/styles.css">
        <!-- Toastr -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
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
                    <th data-field="numerofactura">Número de Factura</th>
                    <th data-field="proveedornombre">Proveedor</th>
                    <th data-field="montobruto">Monto Bruto</th>
                    <th data-field="montoneto">Monto Neto</th>
                    <th data-field="tipopago">Tipo de Pago</th>
                    <th data-field="fechacreacion">Fecha de Creación</th>
                    <th data-field="fechamodificacion">Fecha de Modificación</th>
                    <th>Acciones</th>
                </tr>

<!--tr id="createRow">
                    <td><input type="text" id="newNumeroFactura" placeholder="Número de Factura"></td>
                    <td><input type="text" id="newProveedor" placeholder="Proveedor"></td>
                    <td><input type="number" id="newMontoBruto" placeholder="Monto Bruto"></td>
                    <td><input type="number" id="newMontoNeto" placeholder="Monto Neto"></td>
                    <td>
                        <select id="newTipoPago">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </td>
                    <td>N/A</td>
                    <td>N/A</td>
                    <td>
                        <button onclick="createCompra()">Guardar</button>
                        <button onclick="cancelarCrearCompra()">Cancelar</button>
                    </td>
                </tr>-->
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

    <!-- Toastr Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- Scripts de JavaScript -->
    <script src="./js/utils.js"></script>                   <!-- Utiles para mostrar notificaciones y demás           -->
    <script src="./js/compra/compraGUI.js"></script>       <!-- Manejo dinámico de la página                         -->
    <script src="./js/compra/compraPagination.js"></script><!-- Métodos para Paginación                              -->
    <script src="./js/compra/compraCrud.js"></script>      <!-- Creación, Actualización y Eliminación de Compras     -->
</body>
</html>
