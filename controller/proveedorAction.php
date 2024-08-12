<?php
include __DIR__ . '/../service/proveedorBusiness.php';
require_once __DIR__ . '/../utils/Utils.php';

// Función para validar los datos del proveedor
function validarDatosProveedor($nombre, $email, $fecha) {
    $errors = [];

    if (empty($nombre) || is_numeric($nombre)) {
        $errors[] = "El campo 'Nombre' no puede estar vacío o ser numérico.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El campo 'Correo Electrónico' no es válido.";
    }
    
    if (empty($fecha) || !Utils::validar_fecha($fecha)) {
        $errors[] = "El campo 'Fecha de Registro' no es válido.";
    }

    return $errors;
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = ($_POST['accion']);
    $id = isset ($_POST['id']) ? $_POST['id'] : null;
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $tipo = $_POST['tipo'];
    $fecha = $_POST['fecha_registro'];

    $proveedorBusiness = new ProveedorBusiness();

    if ($accion == 'eliminar') {
        if (empty($id) || !is_numeric($id)) {
            $response['success'] = false;
            $response['message'] = "El ID no puede estar vacío.";
        } else {
            $result = $proveedorBusiness->deleteTBProveedor($id);
            $response['success'] = $result["success"];
            $response['message'] = $result["message"];
        }

    } else {
        $validationErrors = validarDatosProveedor($nombre, $email, $fecha);

        if (empty($validationErrors)) {
            if ($accion == 'insertar') {
                $proveedor = new Proveedor($nombre, $email, $fecha, $id, $tipo);
                $result = $proveedorBusiness->insertTBProveedor($proveedor);
                $response['success'] = $result["success"];
                $response['message'] = $result["message"];
            } elseif ($accion == 'actualizar') {
                $proveedor = new Proveedor($nombre, $email, $fecha, $id, $tipo);
                $result = $proveedorBusiness->updateTBProveedor($proveedor);
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
?>
