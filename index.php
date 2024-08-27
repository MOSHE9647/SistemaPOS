<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Index | POSFusion</title>

        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
        </style>

        <script>
            function printBarcodes(count) {
                const url = `/pdf/print_barcodes.php?count=${count}`;
                window.open(url, '_blank', 'width=800,height=1300');
            }
        </script>
    </head>
    <body>
        <h2>Administración de CRUDS <?php //echo gethostname() ?></h2>
        <ul>
            <li> <a href="./view/impuestoView.php">Gestión de Impuestos</a> </li>
            <li> <a href="./view/proveedorView.php">Gestión de Proveedores</a> </li>
            <li> <a href="./view/direccionView.php">Gestion de Direcciones</a> </li>
            <li> <a href="./view/productoView.php">Gestión de Productos</a> </li>
            <li> <a href="./view/telefonoView.php">Gestión de Telefonos</a> </li>
            <li> <a href="./view/subcategoriaView.php">Gestión de Subcategorias</a> </li>
            <li> <a href="./view/productoSubcategoriaView.php">Gestión de Producto-Subcategorias</a> </li>
            <li> <a href="./view/proveedorProductoView.php">Gestión Proveedor-Producto</a> </li>
            <li> <a href="./view/loteView.php">Gestión de lotes</a> </li>
            <li> <a href="./view/tipoCompraView.php">Gestión de TipoCompra</a> </li>
            <li> <a href="./view/categoriaView.php">Gestion de Categorias</a> </li>
            <li> <a href="" onclick="printBarcodes(10)">Imprimir Códigos de Barras</a> </li>
        </ul>
    </body>
</html>