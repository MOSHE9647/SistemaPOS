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
                const url = `/pdf/printBarcodes.php?count=${count}`;
                window.open(url, '_blank', 'width=800,height=1300');
            }
        </script>
    </head>
    <body>
        <h2>Administración de CRUDS <?php //echo gethostname() ?></h2>
        <ul>
            <h3>Tablas Principales</h3>
            <li> <a href="./view/impuestoView.php">Gestión de Impuestos</a> </li>
            <li> <a href="./view/proveedorView.php">Gestión de Proveedores</a> </li>
            <li> <a href="./view/direccionView.php">Gestion de Direcciones</a> </li>
            <li> <a href="./view/productoView.php">Gestión de Productos</a> </li>
            <li> <a href="./view/telefonoView.php">Gestión de Telefonos</a> </li>
            <li> <a href="./view/categoriaView.php">Gestion de Categorias</a> </li>
            <li> <a href="./view/subcategoriaView.php">Gestión de Subcategorias</a> </li>
            <li> <a href="./view/loteView.php">Gestión de Lotes</a> </li>
            <li> <a href="./view/compraView.php">Gestión de Compras</a> </li>

            <h3>Tablas Intermedias</h3>
            <li> <a href="./view/productoSubcategoriaView.php">Gestión de Producto-Subcategorias</a> </li>
            <li> <a href="./view/proveedorDireccionView.php">Gestión de Proveedor-Direcciones</a> </li>
            <li> <a href="./view/proveedorTelefonoView.php">Gestión de Proveedor-Telefonos</a> </li>
            <li> <a href="./view/proveedorProductoView.php">Gestión de Proveedor-Productos</a> </li>

            <h3>Extras</h3>
            <li> <a href="" onclick="printBarcodes(10)">Imprimir Códigos de Barras</a> </li>
        
            <h3>Pruebas</h3>
            <li> <a href="./test/prueba.php">Prueba de Código de Barras</a> </li>
        </ul>
    </body>
</html>