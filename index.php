<?php
    require_once __DIR__ . '/utils/Variables.php';
    session_start();

    // Función para generar un mensaje basado en los parámetros de sesión
    function getSessionMessage() {
        if (isset($_SESSION[SESSION_ACCESS_DENIED])) {
            unset($_SESSION[SESSION_ACCESS_DENIED]);
            return ['message' => 'No tiene permiso para acceder a esta página', 'type' => 'error'];
        }
        if (isset($_GET[SESSION_LOGGED_OUT])) {
            return ['message' => 'Se ha cerrado la sesión', 'type' => 'info'];
        }
        if (isset($_GET[SESSION_LOGGED_IN])) {
            return ['message' => 'Sesión iniciada correctamente', 'type' => 'success'];
        }
        return null;
    }

    $sessionMessage = getSessionMessage();

    // Parámetros que deben ser eliminados de la URL después de ser utilizados
    $urlParamsToRemove = [SESSION_LOGGED_OUT, SESSION_LOGGED_IN];
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

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="./view/js/utils.js"></script>
        <script>
            function printBarcodes(count) {
                const url = `/pdf/printBarcodes.php?count=${count}`;
                window.open(url, '_blank', 'width=800,height=1300');
            }

            // Función para eliminar múltiples parámetros de la URL
            function removeUrlParams(params) {
                const url = new URL(window.location);
                params.forEach(param => url.searchParams.delete(param)); // Elimina cada parámetro
                window.history.replaceState({}, '', url); // Reemplaza la URL sin los parámetros
            }

            // Ejecutar si hay un mensaje de sesión
            document.addEventListener("DOMContentLoaded", function() {
                <?php if ($sessionMessage): ?>
                    showMessage('<?= $sessionMessage['message'] ?>', '<?= $sessionMessage['type'] ?>');

                    // Elimina los parámetros de sesión una vez usados
                    removeUrlParams(<?= json_encode($urlParamsToRemove) ?>);
                <?php endif; ?>
            });
        </script>
    </head>
    <body>
        <h2>Administración de CRUDS</h2>
        <ul>
            <?php
                // Array de enlaces principales
                $mainTables = [
                    'auth/login.php' => 'Iniciar Sesión',
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
                ];

                // Array de enlaces intermedios
                $intermediateTables = [
                    'productoSubcategoriaView.php' => 'Gestión de Producto-Subcategorías',
                    'proveedorDireccionView.php' => 'Gestión de Proveedor-Direcciones',
                    'proveedorTelefonoView.php' => 'Gestión de Proveedor-Teléfonos',
                    'proveedorProductoView.php' => 'Gestión de Proveedor-Productos',
                    'proveedorCategoriaView.php' => 'Gestión de Proveedor-Categoría',
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
            <li><a href="#" onclick="printBarcodes(10)">Imprimir Códigos de Barras</a></li>

            <h3>Pruebas</h3>
            <li><a href="./view/generarCodigoBarrasView.php">Prueba de Código de Barras</a></li>

            <?php if (isset($_SESSION[SESSION_AUTHENTICATED]) && $_SESSION[SESSION_AUTHENTICATED] === true): ?>
                <h3>Perfil</h3>
                <li><a href="./view/auth/logout.php">Cerrar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </body>
</html>