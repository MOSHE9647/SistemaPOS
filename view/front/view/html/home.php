<!-- Contenido de la pagina -->
<div class="page-content">
    <div class="product-barcode">
        <input type="text" placeholder="Ingrese el código de barras o el nombre del producto" class="barcode-input">
        <button class="barcode-button">Buscar</button>
    </div>
    <div class="sales-container">
        <!-- Lista de Productos -->
        <div class="card">
            <?php for ($i = 0; $i < 10; $i++): ?>
            <div class="product-card">
                <div class="card-header">
                    <div class="product-img" style="background-image: url(<?= $productImage ?>);"></div>
                </div>
                <div class="card-body">
                    <div class="card-title">
                        <h2>Producto <?= $i + 1 ?></h2>
                        <small>Código: <span>1234567890123</span>, </small>
                        <small>Marca: <span>Dos Pinos</span></small>
                    </div>
                    <p>Duis mollit do veniam pariatur ex excepteur cupidatat voluptate ad ut.</p>
                    <h3>Precio: <span>&#162;1500.00</span></h3>
                </div>
            </div>
            <?php endfor; ?>
        </div>
        <!-- Venta de Productos -->
        <div class="card">
            <div class="sales">
                <div class="sales-header">
                    <h2>Venta de Productos</h2>
                </div>
                <!-- <div class="sales-list empty">
                    <p>No hay productos en la lista de venta.</p>
                </div> -->
                <div class="sales-list">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="sales-item">
                        <div class="sales-item-img" style="background-image: url(<?= $productImage ?>);"></div>
                        <div class="sales-item-body">
                            <div class="sales-item-content">
                                <div class="sales-item-info">
                                    <h3>Producto <?= $i + 1 ?></h3>
                                    <small>Código: <span>1234567890123</span></small>
                                    <small>Marca: <span>Dos Pinos</span></small>
                                </div>

                                <div class="sales-item-quantity">
                                    <button class="quantity-button">-</button>
                                    <input type="number" value="1" class="quantity-input">
                                    <button class="quantity-button">+</button>
                                    <button class="remove-button las la-trash"></button>
                                </div>
                            </div>

                            <p>Precio:
                                <span class="quantity">1 x </span>
                                <span>&#162;1500.00</span>
                            </p>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="sales-body">
                    <div class="sales-buttons">
                        <div class="sales-total">
                            <h3>Total: <span>&#162;0.00</span></h3>
                        </div>
                        <button class="sales-button">Finalizar Venta</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>