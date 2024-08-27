<?php

require_once __DIR__ . '/../service/CompraProductoBusiness.php';
require_once __DIR__ . '/../domain/CompraProducto.php';
require_once __DIR__ . '/../utils/Variables.php';

// Función para validar los datos de CompraProducto
function validarDatosCompraProducto($cantidad, $proveedorId) {
    $errors = [];

    if (empty($cantidad) || !is_numeric($cantidad) || $cantidad <= 0) {
        $errors[] = "La cantidad debe ser un número positivo.";
    }

    if (empty($proveedorId) || !is_numeric($proveedorId)) {
        $errors[] = "El ID del proveedor no es válido.";
    }

    return $errors;
}
//
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];

    $compraProductoId = isset($_POST['compraproductoid']) ? $_POST['compraproductoid'] : null;
    $cantidad = $_POST['compraproductocantidad'];
    $proveedorId = $_POST['compraproductoproveedorid'];

    $compraProductoBusiness = new CompraProductoBusiness();

    if ($accion == 'eliminar') {
        // Eliminación lógica
        if (empty($compraProductoId) || !is_numeric($compraProductoId)) {
            $response['success'] = false;
            $response['message'] = "El ID de la compra de producto no puede estar vacío.";
        } else {
            $result = $compraProductoBusiness->eliminarCompraProducto($compraProductoId);
            $response['success'] = $result["success"];
            $response['message'] = $result["message"];
        }
    } else {
        // Validar datos
        $validationErrors = validarDatosCompraProducto($cantidad, $proveedorId);
        if (empty($validationErrors)) {
            // Crear objeto CompraProducto
            $compraProducto = new CompraProducto($cantidad, $proveedorId, null,1, $compraProductoId);

            if ($accion == 'insertar') {
                $result = $compraProductoBusiness->insertarCompraProducto($compraProducto);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } elseif ($accion == 'actualizar') {
                $result = $compraProductoBusiness->actualizarCompraProducto($compraProducto);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } else {
                $response['success'] = false;
                $response['message'] = "Acción no válida.";
            }
        } else {
            $response['success'] = false;
            $response['message'] = implode(' ', $validationErrors);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $compraProductoBusiness = new CompraProductoBusiness();

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarProveedores') {
        // Obtener lista de proveedores
        $result = $compraProductoBusiness->obtenerListaProveedores();
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    } else {
        // Obtener lista de compras de productos
        $result = $compraProductoBusiness->obtenerListaCompraProducto();
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
}
?>
