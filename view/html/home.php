<!-- Contenido de la pagina -->
<div class="page-content table-card">
    <!-- Tabla 'Venta de Producto' -->
    <div class="records table-responsive">
        <div class="table-container">
            <div class="table-header">
                <!-- Barra de busqueda -->
                <div class="sales-search-bar">
                    <input type="text" id="sales-search-input" placeholder="Ingrese el código de barras del producto...">
                    <button class="search-button" id="sales-add-button">
                        <span class="las la-check"></span>
                        <span>Agregar</span>
                    </button>
                    <button class="search-button" id="sales-search-button">
                        <span class="las la-search"></span>
                        <span>Buscar</span>
                    </button>
                </div>
            </div>

            <div class="tab-container">
                <div class="tab-buttons" id="tab-buttons">
                    <button class="tab-button active">
                        <span>Ticket 1</span>
                        <span class="delete-tab las la-times" style="display: none;"></span>
                    </button>
                    <button id="add-tab" onclick="tabs.addTab()">+</button>
                </div>

                <div id="tab1" class="tab-content active">
                    <!-- Tabla de ventas -->
                    <table class="table-sales" width="100%">
                        <thead>
                            <tr>
                                <th data-field="codigo">C&oacute;digo de Barras</th>
                                <th data-field="imagen">Imagen</th>
                                <th data-field="nombre">Nombre del Producto</th>
                                <th data-field="precio">Precio Unitario</th>
                                <th data-field="cantidad">Cantidad</th>
                                <th data-field="preciobruto">Subtotal (sin IVA)</th>
                                <th data-field="impuesto">Importe</th>
                                <th data-field="cantidad">Existencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="table-sales-body" class="table-sales-body">
                            <!-- Contenido de la tabla (se carga dinámicamente con JS) -->
                            <tr>
                                <td colspan="9" class="nodata">
                                    <i class="la la-box"></i>
                                    <p>No se han agregado productos</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Información de la venta (Total, Subtotal, Impuestos) -->
                <div class="sales-info">
                    <div class="sales-price-info">
                        <div class="sales-price subtotal">
                            <span>Subtotal:</span>
                            <span id="sales-subtotal">&#162;0.00</span>
                        </div>
                        <div class="sales-price total">
                            <span>Total:</span>
                            <span id="sales-total">&#162;0.00</span>
                        </div>
                    </div>
                    <div class="sales-buttons">
                        <button class="sales-button" id="sales-reprint-button">
                            <span class="las la-print icon"></span>
                            <span>Reimprimir Ticket</span>
                        </button>
                        <button class="sales-button" id="sales-return-button">
                            <span class="las la-undo icon"></span>
                            <span>Devoluciones</span>
                        </button>
                        <button class="sales-button" id="sales-charge-button">
                            <span class="las la-credit-card icon"></span>
                            <span>Cobrar</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Información de la última venta -->
            <div class="last-sale-card">
                <span class="last-sale-title">Informaci&oacute;n de la &uacute;ltima venta:</span>
                <div class="last-sale-info">
                    <div class="last-sale total">
                        <span>Total:</span>
                        <span id="last-sale-total">&#162;0.00</span>
                    </div>
                    <div class="last-sale paid-with">
                        <span>Pag&oacute; con:</span>
                        <span id="last-sale-date">&#162;0.00</span>
                    </div>
                    <div class="last-sale change">
                        <span>Cambio:</span>
                        <span id="last-sale-change">&#162;0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    

    // function updateDeleteButtons() {
    //     const deleteButtons = document.getElementsByClassName("delete-tab");
    //     for (let deleteButton of deleteButtons) {
    //         deleteButton.style.display = deleteButtons.length > 1 ? "inline" : "none";
    //     }
    // }

    // document.getElementById("add-tab").addEventListener("click", addTab);
    // updateDeleteButtons();
</script>