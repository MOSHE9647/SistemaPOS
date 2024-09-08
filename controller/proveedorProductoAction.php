<?php
require_once __DIR__ . '/../service/proveedorProductoBusiness.php';
require_once __DIR__ . '/../utils/Variables.php';

// Función para validar los datos de ProveedorProducto
function validarDatosProveedorProducto($proveedorId, $productoId) {
    $errors = [];

    if (empty($proveedorId) || !is_numeric($proveedorId)) {
        $errors[] = "El campo 'Proveedor ID' no es válido.";
    }

    if (empty($productoId) || !is_numeric($productoId)) {
        $errors[] = "El campo 'Producto ID' no es válido.";
    }

    return $errors;
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $proveedorProductoId = isset($_POST['proveedorproductoid']) ? $_POST['proveedorproductoid'] : null;
    $proveedorId = $_POST['proveedorid'];
    $productoId = $_POST['productoid'];

    $proveedorProductoBusiness = new ProveedorProductoBusiness();

    if ($accion == 'eliminar') {
        if (empty($proveedorProductoId) || !is_numeric($proveedorProductoId)) {
            $response['success'] = false;
            $response['message'] = "El ID de ProveedorProducto no puede estar vacío.";
        } else {
            $result = $proveedorProductoBusiness->eliminarProveedorProducto($proveedorProductoId);
            $response['success'] = $result["success"];
            $response['message'] = $result["message"];
        }
    } else {
        $validationErrors = validarDatosProveedorProducto($proveedorId, $productoId);

        if (empty($validationErrors)) {
            if ($accion == 'insertar') {
                $result = $proveedorProductoBusiness->insertarProveedorProducto($proveedorId, $productoId);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } elseif ($accion == 'actualizar') {
                $result = $proveedorProductoBusiness->actualizarProveedorProducto($proveedorProductoId, $proveedorId, $productoId);
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
    $proveedorProductoBusiness = new ProveedorProductoBusiness();
    if (isset($_GET['id'])) {
        $proveedorProductoId = $_GET['id'];
        $result = $proveedorProductoBusiness->obtenerProveedorProductoPorId($proveedorProductoId);
        echo json_encode($result);
    } else {
        $result = $proveedorProductoBusiness->obtenerTodosProveedorProducto();
        echo json_encode($result);
    }
    exit();
}
?>
