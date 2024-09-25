<!DOCTYPE html>
<html lang="es-cr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Index | POSFusion</title>

        <link rel="stylesheet" href="./view/css/toast.css">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
        </style>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            function printBarcodes(count, productoID) {
                const url = `/pdf/printBarcodes.php?count=${count}&producto=${productoID}`;
                window.open(url, '_blank', 'width=800,height=1300');
            }
        </script>
    </head>
    <body>
        <h2>Administración de CRUDS</h2>
        <ul>
            <?php
                // Array de enlaces principales
                $mainTables = [
                    'rolUsuarioView.php' => 'Gestión de Roles de Usuario',
                    'usuarioView.php' => 'Gestión de Usuarios',
                    'impuestoView.php' => 'Gestión de Impuestos',
                    'proveedorView.php' => 'Gestión de Proveedores',
                    'direccionView.php' => 'Gestión de Direcciones',
                    'productoView.php' => 'Gestión de Productos',
                    'telefonoView.php' => 'Gestión de Teléfonos',
                    'categoriaView.php' => 'Gestión de Categorías',
                    'subcategoriaView.php' => 'Gestión de Subcategorías',
                    'loteView.php' => 'Gestión de Lotes',
                    'compraView.php' => 'Gestión de Compras',
                    'cuentaporpagarView.php' => 'Gestión de Cuentas por Pagar',
                    'compradetalleView.php' => 'Gestión de Detalle de Compras',
                    'presentacion.php'=> 'Gestion de Presentaciones',
                    'marca.php'=> 'Gestion de Marcas',
                ];

                // Array de enlaces intermedios
                $intermediateTables = [
                    'productoSubcategoriaView.php' => 'Gestión de Producto-Subcategorías',
                    'proveedorDireccionView.php' => 'Gestión de Proveedor-Direcciones',
                    'proveedorTelefonoView.php' => 'Gestión de Proveedor-Teléfonos',
                    'proveedorProductoView.php' => 'Gestión de Proveedor-Productos',
                    'proveedorCategoriaView.php' => 'Gestión de Proveedor-Categoría',
                    'usuarioTelefonoView.php' => 'Gestión de Usuario-Teléfonos',
                ];

                // Función para generar la lista de enlaces
                function generateLinks($links) {
                    foreach ($links as $file => $label) {
                        echo "<li><a href=\"./view/$file\">$label</a></li>";
                    }
                }

                // Imprimir secciones de enlaces
                echo "<h3>Tablas Principales</h3>";
                generateLinks($mainTables);

                echo "<h3>Tablas Intermedias</h3>";
                generateLinks($intermediateTables);
            ?>
            <h3>Extras</h3>
            <li><a href="#" onclick="printBarcodes(10, 2)">Imprimir Códigos de Barras</a></li>
            <li><a href="./view/generarCodigoBarrasView.php">Generar Código de Barras</a></li>
            
            <h3>Front</h3>
            <li><a href="./view/front/index.php">Nuevo Front (En Desarrollo)</a></li>
        </ul>
    </body>
</html>