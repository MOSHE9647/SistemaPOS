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
    </head>
    <body>
        <h2>Administración de CRUDS <?php //echo gethostname() ?></h2>
        <ul>
            <li> <a href="./view/impuestoView.php">Gestión de Impuestos</a> </li>
            <li> <a href="./view/proveedorView.php">Gestión de Proveedores</a> </li>
            <li> <a href="./view/direccionView.php">Gestion de Direcciones</a> </li>
            <li> <a href="./view/productoView.php">Gestión de Productos</a> </li>
            <li> <a href="./view/proveedorTelefonoView.php">Gestión de Telefonos</a> </li>
            <li> <a href="./view/proveedorProductoView.php">Gestión Proveedor-Producto</a> </li>

        </ul>
    </body>
</html>

<!-- NO TOCAR ESTO -->

<?php

    // include_once "utils/Utils.php";
    // $code = '200456789012';

    // $barcode = Utils::generateEAN13Barcode($code);
?>

<!-- <div style="display: flex; flex-direction: column; width: 190px; align-items: center; border: 3px solid black; padding: 20px; border-radius: 5px;">
    <?php 
        // echo '<img src="data:image/png;base64,'.base64_encode($barcode['png']).'">';
        // echo '<span style="font-family: Cascadia Mono; font-size: 18px; letter-spacing: 3px;">' . $barcode['barcode'] . '</span>';            
    ?>
</div> -->