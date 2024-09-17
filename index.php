<?php
    require_once __DIR__ . '/utils/Variables.php';
    session_start();
?>

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
            <li> <a href="./view/usuarioView.php">Gestión de Usuarios</a> </li>
            <li> <a href="./view/impuestoView.php">Gestión de Impuestos</a> </li>
            <li> <a href="./view/proveedorView.php">Gestión de Proveedores</a> </li>
            <li> <a href="./view/direccionView.php">Gestion de Direcciones</a> </li>
            <li> <a href="./view/productoView.php">Gestión de Productos</a> </li>
            <li> <a href="./view/telefonoView.php">Gestión de Telefonos</a> </li>
            <li> <a href="./view/categoriaView.php">Gestion de Categorias</a> </li>
            <li> <a href="./view/subcategoriaView.php">Gestión de Subcategorias</a> </li>
            <li> <a href="./view/loteView.php">Gestión de Lotes</a> </li>
            <li> <a href="./view/compraView.php">Gestión de Compras</a> </li>
            <li> <a href="./view/cuentaporpagarView.php">Gestión de cuentas por pagar</a> </li>
            <li> <a href="./view/compradetalleView.php">Gestión de detalle de compras</a> </li>

            <h3>Tablas Intermedias</h3>
            <li> <a href="./view/productoSubcategoriaView.php">Gestión de Producto-Subcategorias</a> </li>
            <li> <a href="./view/proveedorDireccionView.php">Gestión de Proveedor-Direcciones</a> </li>
            <li> <a href="./view/proveedorTelefonoView.php">Gestión de Proveedor-Telefonos</a> </li>
            <li> <a href="./view/proveedorProductoView.php">Gestión de Proveedor-Productos</a> </li>
            <li> <a href="./view/proveedorCategoriaView.php">Gestion de Proveedor-Categoria</a><li>
            <h3>Extras</h3>
            <li> <a href="" onclick="printBarcodes(10)">Imprimir Códigos de Barras</a> </li>
        
            <h3>Pruebas</h3>
            <li> <a href="./view/generarCodigoBarrasView.php">Prueba de Código de Barras</a> </li>
            
            <?php
                if (isset($_SESSION[SESSION_AUTHENTICATED]) && $_SESSION[SESSION_AUTHENTICATED] === true) {
                    echo '<h3>Perfil</h3>';
                    echo '<li> <a href="./view/auth/logout.php">Cerrar Sesión</a> </li>';
                }
            ?>
        </ul>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="./view/js/utils.js"></script>

        <!-- Muestra un mensaje de error en caso de acceso denegado -->
        <script defer>
            <?php
                // Obtiene el estado de la sesión
                $accessDenied = isset($_SESSION[SESSION_ACCESS_DENIED]) ? $_SESSION[SESSION_ACCESS_DENIED] : false;
                $loggedOut = isset($_GET[SESSION_LOGGED_OUT]) ? boolval($_GET[SESSION_LOGGED_OUT]) : false;

                // Crear variables JavaScript
                echo $accessDenied ? "const accessDenied = true;" : "const accessDenied = false;";
                echo $loggedOut ? "const loggedOut = true;" : "const loggedOut = false;";

                // Elimina las variables de sesión
                if ($accessDenied) unset($_SESSION[SESSION_ACCESS_DENIED]);
            ?>

            // Obtiene el mensaje correspondiente
            const message = 
                accessDenied ? 'No tiene permiso para acceder a esta página' : 
                (loggedOut ? 'Se ha cerrado la sesión' : null);

            if (message !== null) { 
                // Muestra el mensaje
                const type = accessDenied ? 'error' : 'info';
                showMessage(message, type);

                // Elimina el parámetro de la URL después de mostrar el mensaje
                const url = new URL(window.location);
                url.searchParams.delete('<?= SESSION_LOGGED_OUT ?>');  // Elimina el parámetro de la URL
                window.history.replaceState({}, '', url);  // Reemplaza la URL actual sin el parámetro
            }
        </script>

    </body>
</html>